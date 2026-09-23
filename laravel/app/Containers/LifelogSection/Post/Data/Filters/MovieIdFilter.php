<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter;
use Illuminate\Database\Eloquent\Builder;

class MovieIdFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $movieId = $this->normalize($value);

        if ($movieId === null) {
            return;
        }

        $query->whereHas('movies', static function (Builder $builder) use ($movieId): void {
            $builder->where('movies.id', $movieId);
        });
    }

    private function normalize(mixed $value): ?int
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if ($value === null || $value === '') {
            return null;
        }

        if (!is_numeric($value)) {
            return null;
        }

        $movieId = (int) $value;

        return $movieId > 0 ? $movieId : null;
    }
}
