<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;

class DateFromFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $date = $this->normalizeDate($value);
        if ($date === null) {
            return;
        }

        $query->whereDate('date', '>=', $date);
    }

    private function normalizeDate(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        try {
            return Carbon::parse((string) $value)->toDateString();
        } catch (\Throwable) {
            return null;
        }
    }
}
