<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\Tasks;

use App\Containers\MovieSection\Episode\Data\DTO\EpisodeCreateData;
use App\Containers\MovieSection\Episode\Data\Repositories\EpisodeRepository;
use App\Containers\MovieSection\Episode\Models\Episode;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateEpisodeTask extends ParentTask
{
    public function __construct(
        private readonly EpisodeRepository $episodeRepository,
    ) {
    }

    public function run(EpisodeCreateData $dto): Episode
    {
        return $this->episodeRepository->create($dto);
    }
}
