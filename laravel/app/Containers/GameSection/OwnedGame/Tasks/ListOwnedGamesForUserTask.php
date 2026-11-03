<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\Tasks;

use App\Containers\GameSection\OwnedGame\Data\Repositories\OwnedGameRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListOwnedGamesForUserTask extends ParentTask
{
    public function __construct(
        private readonly OwnedGameRepository $ownedGameRepository,
    ) {}

    public function run(int $userId): Collection
    {
        return $this->ownedGameRepository->getForUser($userId);
    }
}
