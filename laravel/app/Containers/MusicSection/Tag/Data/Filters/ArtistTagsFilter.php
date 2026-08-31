<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\Filters;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Ship\Parents\QueryBuilder\Filter as FilterInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class ArtistTagsFilter implements FilterInterface
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        $ids = $this->normalizeIds($value);
        if ($ids === []) {
            return;
        }

        $match = $this->matchMode($value);
        $nested = $this->nestedEnabled($value);

        if ($match === 'and') {
            foreach ($ids as $id) {
                $set = $nested ? $this->withDescendants([$id]) : [$id];
                $query->whereHas('tags', function (Builder $tagQuery) use ($set) {
                    $tagQuery->whereIn('music_tags.id', $set);
                });
            }

            return;
        }

        $set = $nested ? $this->withDescendants($ids) : $ids;
        $query->whereHas('tags', function (Builder $tagQuery) use ($set) {
            $tagQuery->whereIn('music_tags.id', $set);
        });
    }

    /**
     * @return list<int>
     */
    private function normalizeIds(mixed $value): array
    {
        if (is_array($value) && array_key_exists('ids', $value)) {
            $value = $value['ids'];
        }

        if (is_string($value) || is_numeric($value)) {
            $value = preg_split('/\s*,\s*/', (string) $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatten()
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
    }

    private function matchMode(mixed $value): string
    {
        $mode = is_array($value) ? ($value['match'] ?? null) : null;
        $mode = $mode ?: request()->input('filter.tags_match', 'or');

        return $mode === 'and' ? 'and' : 'or';
    }

    private function nestedEnabled(mixed $value): bool
    {
        $nested = is_array($value) ? ($value['nested'] ?? null) : null;
        if ($nested === null) {
            $nested = request()->input('filter.tags_nested', false);
        }

        return filter_var($nested, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @param list<int> $ids
     * @return list<int>
     */
    private function withDescendants(array $ids): array
    {
        $childrenByParent = MusicTag::query()
            ->get(['id', 'parent_id'])
            ->groupBy(fn (MusicTag $tag) => (int) $tag->parent_id);

        $result = [];
        $queue = $ids;
        $seen = [];

        while ($queue !== []) {
            $id = array_shift($queue);
            if (isset($seen[$id])) {
                continue;
            }

            $seen[$id] = true;
            $result[] = $id;

            /** @var Collection<int, MusicTag> $children */
            $children = $childrenByParent->get($id, collect());
            foreach ($children as $child) {
                $queue[] = (int) $child->id;
            }
        }

        return $result;
    }
}
