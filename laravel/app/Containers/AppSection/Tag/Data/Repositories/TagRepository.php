<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\Repositories;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class TagRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Tag::class)
                           ->allowedSorts(['name', 'created_at', 'updated_at'])
                           ->get();
    }

    public function getMostUsedTagsForUser(int $userId, int $limit = 10): Collection
    {
        return Tag::query()
            ->select('tags.*', DB::raw('COUNT(taggables.tag_id) as usage_count'))
            ->leftJoin('taggables', function ($join) use ($userId) {
                $join->on('tags.id', '=', 'taggables.tag_id')
                     ->where('taggables.user_id', '=', $userId);
            })
            ->groupBy('tags.id')
            ->orderByDesc('usage_count')
            ->limit($limit)
            ->get();
    }

    public function create(array $data)
    {
        return Tag::create($data);
    }

    public function delete(string $id): int
    {
        return Tag::whereId($id)->delete();
    }
}
