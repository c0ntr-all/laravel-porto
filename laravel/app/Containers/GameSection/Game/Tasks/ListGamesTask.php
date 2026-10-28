<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Tasks;

use App\Containers\GameSection\Game\Data\Repositories\GameRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListGamesTask extends ParentTask
{
    public function __construct(
        private readonly GameRepository $gameRepository,
    ) {}

    public function run(): Collection
    {
        return $this->gameRepository->get();
    }
}
