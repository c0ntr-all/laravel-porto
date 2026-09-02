<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter as FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class TrackRateFilter implements FilterInterface
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $rates = $this->normalizeRates($value);
        if ($rates === []) {
            return;
        }

        $includeUnrated = in_array(0, $rates, true);
        $ratedValues = array_values(array_filter($rates, fn (int $rate) => $rate > 0));

        $query->where(function (Builder $group) use ($includeUnrated, $ratedValues): void {
            if ($ratedValues !== []) {
                $group->whereHas('rate', function (Builder $rateQuery) use ($ratedValues): void {
                    $rateQuery->whereIn('rate', $ratedValues);
                });
            }

            if ($includeUnrated) {
                $method = $ratedValues !== [] ? 'orWhereDoesntHave' : 'whereDoesntHave';
                $group->{$method}('rate');
            }
        });
    }

    /**
     * @return list<int>
     */
    private function normalizeRates(mixed $value): array
    {
        if (is_string($value) || is_numeric($value)) {
            $value = preg_split('/\s*,\s*/', (string) $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatten()
            ->map(fn (mixed $rate) => (int) $rate)
            ->filter(fn (int $rate) => $rate >= 0 && $rate <= 4)
            ->unique()
            ->values()
            ->all();
    }
}
