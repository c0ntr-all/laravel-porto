<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Tasks;

use App\Containers\GameSection\Game\Data\DTO\GameCreateData;
use App\Containers\GameSection\Game\Data\Repositories\GameRepository;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateGameTask extends ParentTask
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {}

    public function run(GameCreateData $dto): Game
    {
        return $this->gameRepository->create($dto);
    }
}
