<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Tasks;

use App\Containers\GameSection\Achievement\Data\DTO\AchievementUpdateData;
use App\Containers\GameSection\Achievement\Data\Repositories\AchievementRepository;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateAchievementTask extends ParentTask
{
    public function __construct(
        private readonly AchievementRepository $achievementRepository,
    ) {}

    public function run(Achievement $achievement, AchievementUpdateData $dto): Achievement
    {
        return $this->achievementRepository->update($achievement, $dto);
    }
}
