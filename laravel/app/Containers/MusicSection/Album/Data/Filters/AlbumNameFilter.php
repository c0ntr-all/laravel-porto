<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter as FilterInterface;
use Illuminate\Database\Eloquent\Builder;

class AlbumNameFilter implements FilterInterface
{
    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        unset($property);

        $terms = $this->terms($value);
        if ($terms === []) {
            return;
        }

        $query->where(function (Builder $outer) use ($terms): void {
            foreach ($terms as $index => $term) {
                $pattern = $this->like($term);
                $method = $index === 0 ? 'where' : 'orWhere';

                $outer->{$method}(function (Builder $group) use ($pattern): void {
                    $group->where(function (Builder $fields) use ($pattern): void {
                        $this->matchNameOrEdition($fields, $pattern);
                    })->orWhereHas('versions', function (Builder $versions) use ($pattern): void {
                        $versions->where(function (Builder $fields) use ($pattern): void {
                            $this->matchNameOrEdition($fields, $pattern);
                        });
                    });
                });
            }
        });
    }

    private function matchNameOrEdition(Builder $query, string $pattern): void
    {
        $grammar = $query->getQuery()->getGrammar();
        $name = $grammar->wrap($query->qualifyColumn('name'));
        $edition = $grammar->wrap($query->qualifyColumn('edition'));

        $query->whereRaw("LOWER({$name}) LIKE ?", [$pattern])
              ->orWhereRaw("LOWER({$edition}) LIKE ?", [$pattern]);
    }

    /**
     * @return list<string>
     */
    private function terms(mixed $value): array
    {
        $values = is_array($value) ? $value : [$value];
        $terms = [];

        foreach ($values as $item) {
            if (!is_string($item) && !is_numeric($item)) {
                continue;
            }

            $term = trim((string) $item);
            if ($term !== '') {
                $terms[] = $term;
            }
        }

        return array_values(array_unique($terms));
    }

    private function like(string $term): string
    {
        return '%'.addcslashes(mb_strtolower($term, 'UTF-8'), '%_\\').'%';
    }
}
