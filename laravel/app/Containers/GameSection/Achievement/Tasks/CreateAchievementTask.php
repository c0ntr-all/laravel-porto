<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Tasks;

use App\Containers\GameSection\Achievement\Data\DTO\AchievementCreateData;
use App\Containers\GameSection\Achievement\Data\Repositories\AchievementRepository;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateAchievementTask extends ParentTask
{
    public function __construct(
        private readonly AchievementRepository $achievementRepository,
    ) {}

    public function run(Game $game, AchievementCreateData $dto): Achievement
    {
        return $this->achievementRepository->create($game, $dto);
    }
}
