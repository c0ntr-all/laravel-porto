<?php declare(strict_types=1);

namespace App\Ship\Support\Search;

use Illuminate\Database\Eloquent\Builder;

/**
 * Shared text search: wrong-layout variants + relevance ranking.
 *
 * Rank (lower is better):
 * 0 - exact title match
 * 1 - term as a leading token (e.g. "Тор: Рагнарёк", "Тор 2")
 * 2 - glued prefix (e.g. "Торнадо")
 * 3 - term as an inner token
 * 4 - substring anywhere
 * 100 - no primary-column match (secondary-only hit)
 */
final class RankedTextSearch
{
    public function __construct(
        private readonly KeyboardLayoutMapper $layoutMapper = new KeyboardLayoutMapper(),
    ) {
    }

    /**
     * @param list<string> $primaryColumns Columns used for matching and ranking (e.g. title)
     * @param list<string> $secondaryColumns Match-only columns with lower priority (e.g. description)
     */
    public function apply(
        Builder $query,
        string $term,
        array $primaryColumns,
        array $secondaryColumns = [],
    ): void {
        $searchQuery = SearchQuery::from($term, $this->layoutMapper);
        if ($searchQuery === null) {
            return;
        }

        $primaryColumns = array_values(array_filter($primaryColumns, static fn (string $c): bool => $c !== ''));
        $secondaryColumns = array_values(array_filter($secondaryColumns, static fn (string $c): bool => $c !== ''));
        $allColumns = array_values(array_unique([...$primaryColumns, ...$secondaryColumns]));

        if ($allColumns === []) {
            return;
        }

        $this->constrainMatches($query, $searchQuery, $allColumns);

        if ($primaryColumns !== []) {
            $this->applyRanking($query, $searchQuery, $primaryColumns);
        }
    }

    /**
     * @param list<string> $columns
     */
    private function constrainMatches(Builder $query, SearchQuery $searchQuery, array $columns): void
    {
        $query->where(function (Builder $outer) use ($searchQuery, $columns): void {
            foreach ($searchQuery->variants as $variant) {
                $like = '%' . $searchQuery->escapeLike($variant) . '%';

                foreach ($columns as $column) {
                    $outer->orWhere($column, 'like', $like);
                }
            }
        });
    }

    /**
     * @param list<string> $primaryColumns
     */
    private function applyRanking(Builder $query, SearchQuery $searchQuery, array $primaryColumns): void
    {
        $cases = [];
        $bindings = [];

        foreach ($primaryColumns as $column) {
            foreach ($searchQuery->variants as $variant) {
                $escaped = $searchQuery->escapeLike($variant);

                $cases[] = <<<SQL
CASE
    WHEN LOWER({$column}) = LOWER(?) THEN 0
    WHEN LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      THEN 1
    WHEN LOWER({$column}) LIKE LOWER(?) THEN 2
    WHEN LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      OR LOWER({$column}) LIKE LOWER(?)
      THEN 3
    WHEN LOWER({$column}) LIKE LOWER(?) THEN 4
    ELSE 100
END
SQL;

                $bindings[] = $variant;
                // rank 1 - leading token separators
                $bindings[] = $escaped . ' %';
                $bindings[] = $escaped . ':%';
                $bindings[] = $escaped . '-%';
                $bindings[] = $escaped . '/%';
                $bindings[] = $escaped . '(%';
                $bindings[] = $escaped . '.%';
                // rank 2 - glued prefix
                $bindings[] = $escaped . '%';
                // rank 3 - inner token
                $bindings[] = '% ' . $escaped . ' %';
                $bindings[] = '% ' . $escaped . ':%';
                $bindings[] = '% ' . $escaped;
                // rank 4 - substring
                $bindings[] = '%' . $escaped . '%';
            }
        }

        $least = 'LEAST(' . implode(', ', $cases) . ')';
        $lengthColumn = $primaryColumns[0];

        $query->orderByRaw("{$least} ASC", $bindings)
            ->orderByRaw("CHAR_LENGTH({$lengthColumn}) ASC");
    }
}
