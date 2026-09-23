<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Tasks;

use App\Containers\MovieSection\Episode\Data\Repositories\EpisodeRepository;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteEpisodeTask extends ParentTask
{
    public function __construct(
        private readonly EpisodeRepository $episodeRepository,
    ) {
    }

    public function run(Episode $episode): bool
    {
        return $this->episodeRepository->delete($episode);
    }
}
