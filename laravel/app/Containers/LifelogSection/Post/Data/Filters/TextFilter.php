<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter;
use Illuminate\Database\Eloquent\Builder;

class TextFilter implements Filter
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $term = $this->normalize($value);
        if ($term === null) {
            return;
        }

        $pattern = '%' . addcslashes($term, '%_\\') . '%';

        $query->where(function (Builder $builder) use ($pattern): void {
            $builder->where('title', 'like', $pattern)
                ->orWhere('content', 'like', $pattern);
        });
    }

    private function normalize(mixed $value): ?string
    {
        if (is_array($value)) {
            $value = $value[0] ?? null;
        }

        if (!is_string($value) && !is_numeric($value)) {
            return null;
        }

        $term = trim((string) $value);

        return $term !== '' ? $term : null;
    }
}
