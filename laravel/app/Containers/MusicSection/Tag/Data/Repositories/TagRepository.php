<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\Repositories;

use App\Containers\MusicSection\Tag\Models\MusicTag;
use Illuminate\Database\Eloquent\Collection;

class TagRepository
{
    public function getTags(): Collection
    {
        return MusicTag::orderBy('name')->get();
    }

    public function getTagsTree(): Collection
    {
        return MusicTag::with('tags')
                       ->whereNull('parent_id')
                       ->get();
    }

    public function createTag($dto)
    {
        return MusicTag::create($dto->toArray());
    }

    public function deleteTags(array $ids): int
    {
        return MusicTag::whereIn('id', $ids)->delete();
    }
}
