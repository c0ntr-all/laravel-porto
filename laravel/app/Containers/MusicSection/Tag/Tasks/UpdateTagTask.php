<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\DTO\TagUpdateData;
use App\Containers\MusicSection\Tag\Data\Repositories\TagRepository;
use App\Containers\MusicSection\Tag\Models\MusicTag;
use App\Ship\Parents\Tasks\Task;

class UpdateTagTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function run(MusicTag $tag, TagUpdateData $dto): MusicTag
    {
        return $this->tagRepository->updateTag($tag, $dto);
    }
}
