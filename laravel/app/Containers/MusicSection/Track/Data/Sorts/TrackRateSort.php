<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Sorts;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Query\JoinClause;
use Spatie\QueryBuilder\Sorts\Sort;

class TrackRateSort implements Sort
{
    private const JOIN_ALIAS = 'user_track_rates';

    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $this->joinCurrentUserRates($query);

        $table = $query->getModel()->getTable();
        $direction = $descending ? 'desc' : 'asc';
        // Personal scale is 1–4. Sentinel keeps unrated rows last in both directions
        // and is a real expression (not a SELECT-only alias), so cursor WHERE works.
        $unrated = $descending ? 0 : 5;

        $query->select($table.'.*')
            ->selectRaw('COALESCE('.self::JOIN_ALIAS.'.rate, '.$unrated.') as user_rate')
            ->orderBy('user_rate', $direction)
            ->orderBy($table.'.id', $direction);
    }

    private function joinCurrentUserRates(Builder $query): void
    {
        if ($this->alreadyJoined($query)) {
            return;
        }

        $table = $query->getModel()->getTable();
        $userId = (int) auth()->id();

        $query->leftJoin('music_track_rates as '.self::JOIN_ALIAS, function (JoinClause $join) use ($table, $userId): void {
            $join->on(self::JOIN_ALIAS.'.track_id', '=', $table.'.id')
                ->where(self::JOIN_ALIAS.'.user_id', '=', $userId);
        });
    }

    private function alreadyJoined(Builder $query): bool
    {
        foreach ($query->getQuery()->joins ?? [] as $join) {
            if (str_contains((string) $join->table, self::JOIN_ALIAS)) {
                return true;
            }
        }

        return false;
    }
}
