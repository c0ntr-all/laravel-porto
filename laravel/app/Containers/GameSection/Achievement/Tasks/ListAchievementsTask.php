<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Tasks;

use App\Containers\GameSection\Achievement\Data\Repositories\AchievementRepository;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class ListAchievementsTask extends ParentTask
{
    public function __construct(
        private readonly AchievementRepository $achievementRepository,
    ) {}

    public function run(Game $game): Collection
    {
        return $this->achievementRepository->getForGame($game);
    }
}
