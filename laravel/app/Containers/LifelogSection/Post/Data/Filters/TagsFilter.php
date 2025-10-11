<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\Filters;

use Spatie\QueryBuilder\Filters\Filter;
use Illuminate\Database\Eloquent\Builder;

class TagsFilter implements Filter
{
    public function __invoke(Builder $query, $value, string $property): void
    {
        $tagIds = is_string($value) ? [$value] : $value;

        $mode = request()->input('filter.tags_mode', 'or');

        if ($mode === 'and') {
            $query->whereHas('tags', function (Builder $q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            }, '=', count($tagIds));
        } else {
            $query->whereHas('tags', function (Builder $q) use ($tagIds) {
                $q->whereIn('tags.id', $tagIds);
            });
        }
    }
}
