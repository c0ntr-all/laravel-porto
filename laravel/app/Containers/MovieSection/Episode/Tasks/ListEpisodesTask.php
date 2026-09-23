<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Tasks;

use App\Containers\MovieSection\Episode\Data\Repositories\EpisodeRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListEpisodesTask extends ParentTask
{
    public function __construct(
        private readonly EpisodeRepository $episodeRepository,
    ) {
    }

    public function run(): Collection
    {
        return $this->episodeRepository->get();
    }
}
