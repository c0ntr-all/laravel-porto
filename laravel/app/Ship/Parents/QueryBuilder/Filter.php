<?php declare(strict_types=1);

namespace App\Ship\Parents\QueryBuilder;

use Spatie\QueryBuilder\Filters\Filter as SpatieFilter;
use Illuminate\Database\Eloquent\Builder;

interface Filter extends SpatieFilter
{
    /**
     * @param Builder $query
     * @param mixed $value
     * @param string $property
     */
    public function __invoke(Builder $query, mixed $value, string $property);
}
