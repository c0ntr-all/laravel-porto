<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\Repositories;

use App\Containers\MusicSection\Tag\Data\DTO\TagGroupCreateData;
use App\Containers\MusicSection\Tag\Data\DTO\TagGroupUpdateData;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\QueryBuilder\AllowedFilter;

class TagGroupRepository
{
    public function getGroups(): Collection
    {
        return QueryBuilder::for(MusicTagGroup::class)
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('is_active'),
                               AllowedFilter::exact('is_system'),
                           ])
                           ->allowedSorts(['name', 'display_order', 'created_at'])
                           ->orderBy('display_order')
                           ->orderBy('name')
                           ->get();
    }

    public function createGroup(TagGroupCreateData $dto): MusicTagGroup
    {
        return MusicTagGroup::create($dto->toArray());
    }

    public function updateGroup(MusicTagGroup $group, TagGroupUpdateData $dto): MusicTagGroup
    {
        $group->update(collect($dto->toArray())->filter(fn (mixed $value) => $value !== null)->all());

        return $group;
    }

    public function deleteGroup(MusicTagGroup $group): ?bool
    {
        return $group->delete();
    }
}
