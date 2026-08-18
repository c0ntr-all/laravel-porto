<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class ListTagsByWhereInTask extends Task
{
    public function run(string $field, array $criteria, ?int $userId = null): Collection
    {
        return QueryBuilder::for(Tag::class)
            ->when($userId !== null, fn (Builder $query) => $query->where('user_id', $userId))
            ->when(!empty($criteria), function (Builder $query) use ($field, $criteria) {
                $query->whereIn($field, $criteria);
            })
            ->get();
    }
}
