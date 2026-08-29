<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\DTO\TagGroupUpdateData;
use App\Containers\MusicSection\Tag\Data\Repositories\TagGroupRepository;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Ship\Parents\Tasks\Task;

class UpdateTagGroupTask extends Task
{
    public function __construct(
        private readonly TagGroupRepository $tagGroupRepository,
    ) {
    }

    public function run(MusicTagGroup $group, TagGroupUpdateData $dto): MusicTagGroup
    {
        return $this->tagGroupRepository->updateGroup($group, $dto);
    }
}
