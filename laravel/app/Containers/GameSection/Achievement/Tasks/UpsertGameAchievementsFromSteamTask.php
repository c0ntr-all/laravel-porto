<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Tasks;

use App\Containers\GameSection\Achievement\Data\Repositories\AchievementRepository;
use App\Containers\GameSection\Achievement\Exceptions\GameMissingSteamIdException;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Steam\Tasks\FetchSteamGameAchievementsTask;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class UpsertGameAchievementsFromSteamTask extends ParentTask
{
    public function __construct(
        private readonly FetchSteamGameAchievementsTask $fetchSteamGameAchievementsTask,
        private readonly AchievementRepository $achievementRepository,
    ) {}

    /**
     * @return array{
     *     achievements: Collection<int, Achievement>,
     *     created: int,
     *     updated: int,
     *     total: int
     * }
     */
    public function run(Game $game, ?string $language = null): array
    {
        if ($game->steam_id === null) {
            throw new GameMissingSteamIdException((int) $game->id);
        }

        $response = $this->fetchSteamGameAchievementsTask->run((int) $game->steam_id, $language);

        $created = 0;
        $updated = 0;
        /** @var Collection<int, Achievement> $achievements */
        $achievements = (new Achievement)->newCollection();

        foreach ($response->achievements as $dto) {
            $result = $this->achievementRepository->upsertFromSteam($game, $dto);
            $achievements->push($result['achievement']);
            if ($result['was_created']) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'achievements' => $achievements,
            'created' => $created,
            'updated' => $updated,
            'total' => $achievements->count(),
        ];
    }
}
