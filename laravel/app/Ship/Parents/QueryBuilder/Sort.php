<?php declare(strict_types=1);

namespace App\Ship\Parents\QueryBuilder;

use Spatie\QueryBuilder\Sorts\Sort as SpatieSort;
use Illuminate\Database\Eloquent\Builder;

interface Sort extends SpatieSort
{
    /**
     * @param Builder $query
     * @param bool $descending
     * @param string $property
     */
    public function __invoke(Builder $query, bool $descending, string $property);
}
