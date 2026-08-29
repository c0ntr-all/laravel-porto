<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\DTO\TagCreateData;
use App\Containers\MusicSection\Tag\Data\Repositories\TagRepository;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Ship\Parents\Tasks\Task;

class CreateTagTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function run(TagCreateData $dto)
    {
        if ($dto->parent_id && !$dto->group_id) {
            $parent = MusicTag::query()->find($dto->parent_id);
            $dto->group_id = $parent?->group_id;
        }

        return $this->tagRepository->createTag($dto);
    }
}
