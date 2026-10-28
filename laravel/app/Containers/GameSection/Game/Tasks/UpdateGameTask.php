<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Tasks;

use App\Containers\GameSection\Game\Data\DTO\GameUpdateData;
use App\Containers\GameSection\Game\Data\Repositories\GameRepository;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateGameTask extends ParentTask
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {}

    public function run(Game $game, GameUpdateData $dto): Game
    {
        return $this->gameRepository->update($game, $dto);
    }
}
