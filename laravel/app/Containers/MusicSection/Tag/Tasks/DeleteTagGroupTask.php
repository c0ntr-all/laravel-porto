<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Tasks;

use App\Containers\MusicSection\Tag\Data\Repositories\TagGroupRepository;
use App\Containers\MusicSection\Tag\Models\MusicTagGroup;
use App\Ship\Exceptions\DeleteResourceFailedException;
use App\Ship\Parents\Tasks\Task;

class DeleteTagGroupTask extends Task
{
    public function __construct(
        private readonly TagGroupRepository $tagGroupRepository,
    ) {
    }

    /**
     * @throws DeleteResourceFailedException
     */
    public function run(MusicTagGroup $group): bool
    {
        if ($group->is_system) {
            throw new DeleteResourceFailedException();
        }

        $deleted = $this->tagGroupRepository->deleteGroup($group);

        if (!$deleted) {
            throw new DeleteResourceFailedException();
        }

        return true;
    }
}
