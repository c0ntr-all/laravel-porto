<?php declare(strict_types=1);

namespace App\Ship\Support\Search;

/**
 * Normalized search input with wrong-layout keyboard variants.
 */
final class SearchQuery
{
    /**
     * @param list<string> $variants
     */
    private function __construct(
        public readonly string $original,
        public readonly array $variants,
    ) {
    }

    public static function from(string $term, ?KeyboardLayoutMapper $mapper = null): ?self
    {
        $original = trim($term);
        if ($original === '') {
            return null;
        }

        $mapper ??= new KeyboardLayoutMapper();

        return new self($original, $mapper->variants($original));
    }

    public function escapeLike(string $value): string
    {
        return addcslashes($value, "%_\\");
    }
}
