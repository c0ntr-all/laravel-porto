<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Tasks;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Data\Repositories\PlayerAchievementRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListPlayerAchievementsForGameTask extends ParentTask
{
    public function __construct(
        private readonly PlayerAchievementRepository $playerAchievementRepository,
    ) {}

    public function run(Game $game, int $userId): Collection
    {
        return $this->playerAchievementRepository->getForGameAndUser($game, $userId);
    }
}
