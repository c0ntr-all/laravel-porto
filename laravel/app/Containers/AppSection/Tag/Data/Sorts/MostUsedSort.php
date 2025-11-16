<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\Sorts;

use App\Ship\Parents\QueryBuilder\Sort;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class MostUsedSort implements Sort
{
    public function __construct(
        private ?int $userId = null
    )
    {
    }
    public function __invoke(Builder $query, bool $descending, string $property): void
    {
        $direction = $descending ? 'DESC' : 'ASC';

        $query->select('tags.*', DB::raw('COUNT(taggables.tag_id) as usage_count'))
              ->leftJoin('taggables', function ($join) {
                  $join->on('tags.id', '=', 'taggables.tag_id')
                       ->where('taggables.user_id', '=', $this->userId);
              })
              ->groupBy('tags.id')->orderBy('usage_count', $direction);
    }
}
