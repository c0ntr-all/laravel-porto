<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\Repositories;

use App\Containers\MusicSection\Tag\Data\DTO\TagCreateData;
use App\Containers\MusicSection\Tag\Data\DTO\TagUpdateData;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class TagRepository
{
    public function getTags(): Collection
    {
        return QueryBuilder::for(MusicTag::class)
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('group_id'),
                               AllowedFilter::exact('is_active'),
                               AllowedFilter::exact('parent_id'),
                           ])
                           ->allowedSorts(['name', 'created_at'])
                           ->orderBy('name')
                           ->get();
    }

    public function getTagsTree(): Collection
    {
        return QueryBuilder::for(MusicTag::class)
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('group_id'),
                               AllowedFilter::exact('is_active'),
                           ])
                           ->allowedSorts(['name', 'created_at'])
                           ->with('tags')
                           ->whereNull('parent_id')
                           ->get();
    }

    public function createTag($dto)
    {
        return MusicTag::create($dto->toArray());
    }

    public function updateTag(MusicTag $tag, TagUpdateData $dto): MusicTag
    {
        $tag->update(collect($dto->toArray())->filter(fn (mixed $value) => $value !== null)->all());

        return $tag;
    }

    public function deleteTags(array $ids): int
    {
        return MusicTag::whereIn('id', $ids)->delete();
    }
}
