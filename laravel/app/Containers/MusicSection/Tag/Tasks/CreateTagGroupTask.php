<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\DTO\TagGroupCreateData;
use App\Containers\MusicSection\Tag\Data\Repositories\TagGroupRepository;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Ship\Parents\Tasks\Task;

class CreateTagGroupTask extends Task
{
    public function __construct(
        private readonly TagGroupRepository $tagGroupRepository,
    ) {
    }

    public function run(TagGroupCreateData $dto): MusicTagGroup
    {
        return $this->tagGroupRepository->createGroup($dto);
    }
}
