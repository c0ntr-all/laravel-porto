<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Sorts;

use App\Ship\Parents\QueryBuilder\Sort;
use Illuminate\Database\Eloquent\Builder;

class DatetimeSort implements Sort
{
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $direction = $descending ? 'desc' : 'asc';
        $table = $query->getModel()->getTable();

        $query
            ->orderBy("{$table}.date", $direction)
            ->orderByRaw("COALESCE({$table}.`time`, '00:00:00') {$direction}")
            ->orderBy("{$table}.id", $direction);
    }
}
