<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\Repositories\TagRepository;
use App\Ship\Parents\Tasks\Task;

class ListTagsShortTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function run(): ?array
    {
        return $this->tagRepository->getTags()?->pluck('id', 'name')->toArray();
    }
}
