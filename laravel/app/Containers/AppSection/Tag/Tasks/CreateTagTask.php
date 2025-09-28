<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Tasks;

use App\Containers\AppSection\Tag\Data\DTO\TagCreateData;
use App\Containers\AppSection\Tag\Data\Repositories\TagRepository;
use App\Ship\Parents\Tasks\Task;

class CreateTagTask extends Task
{
    public function __construct(
        private readonly TagRepository $tagRepository,
    ) {
    }

    public function run(TagCreateData $dto)
    {
        return $this->tagRepository->create($dto);
    }
}
