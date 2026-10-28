<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Tasks;

use App\Containers\GameSection\Game\Data\Repositories\GameRepository;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteGameTask extends ParentTask
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {}

    public function run(Game $game): bool
    {
        return $this->gameRepository->delete($game);
    }
}
