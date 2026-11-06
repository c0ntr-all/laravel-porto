<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\Data\Filters;

use App\Ship\Parents\QueryBuilder\Filter as FilterInterface;
use App\Ship\Support\Search\RankedTextSearch;
use Illuminate\Database\Eloquent\Builder;

class MovieSearchFilter implements FilterInterface
{
    public function __construct(
        private readonly RankedTextSearch $rankedTextSearch = new RankedTextSearch(),
    ) {
    }

    public function __invoke(Builder $query, mixed $value, string $property): void
    {
        if (is_array($value)) {
            $value = $value[0] ?? '';
        }

        if (!is_string($value) && !is_numeric($value)) {
            return;
        }

        $table = $query->getModel()->getTable();

        $this->rankedTextSearch->apply(
            $query,
            (string) $value,
            primaryColumns: ["{$table}.title"],
            secondaryColumns: [
                "{$table}.description",
                "{$table}.short_description",
            ],
        );
    }
}
