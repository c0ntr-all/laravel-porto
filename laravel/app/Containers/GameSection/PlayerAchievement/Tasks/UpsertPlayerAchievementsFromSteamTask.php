<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Tasks;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Data\Repositories\AchievementRepository;
use App\Containers\GameSection\Achievement\Exceptions\GameMissingSteamIdException;
use App\Containers\GameSection\Achievement\Tasks\UpsertGameAchievementsFromSteamTask;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Data\Repositories\PlayerAchievementRepository;
use App\Containers\GameSection\PlayerAchievement\Exceptions\SteamUserNotLinkedException;
use App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement;
use App\Containers\GameSection\Steam\Tasks\FetchSteamPlayerAchievementsTask;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class UpsertPlayerAchievementsFromSteamTask extends ParentTask
{
    public function __construct(
        private readonly AchievementRepository $achievementRepository,
        private readonly PlayerAchievementRepository $playerAchievementRepository,
        private readonly UpsertGameAchievementsFromSteamTask $upsertGameAchievementsFromSteamTask,
        private readonly FetchSteamPlayerAchievementsTask $fetchSteamPlayerAchievementsTask,
    ) {}

    /**
     * @return array{
     *     player_achievements: Collection<int, PlayerAchievement>,
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     catalog_imported: bool,
     *     game_name: string|null
     * }
     */
    public function run(User $user, Game $game, ?string $language = null): array
    {
        if ($game->steam_id === null) {
            throw new GameMissingSteamIdException((int) $game->id);
        }

        $steamUser = $user->steamUser;
        if ($steamUser === null) {
            throw new SteamUserNotLinkedException((int) $user->id);
        }

        $catalogImported = false;
        if ($this->achievementRepository->countForGame($game) === 0) {
            $this->upsertGameAchievementsFromSteamTask->run($game, $language);
            $catalogImported = true;
        }

        $catalog = $this->achievementRepository->mapBySteamApiName($game);
        $response = $this->fetchSteamPlayerAchievementsTask->run(
            (int) $game->steam_id,
            $steamUser->steam_id,
            $language,
        );

        $created = 0;
        $updated = 0;
        $skipped = 0;
        /** @var Collection<int, PlayerAchievement> $playerAchievements */
        $playerAchievements = (new PlayerAchievement)->newCollection();

        foreach ($response->achievements as $dto) {
            $achievement = $catalog->get($dto->apiname);
            if ($achievement === null) {
                $skipped++;

                continue;
            }

            $result = $this->playerAchievementRepository->upsertFromSteam(
                (int) $user->id,
                $achievement,
                $dto,
            );
            $playerAchievements->push($result['player_achievement']->loadMissing('achievement'));

            if ($result['was_created']) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'player_achievements' => $playerAchievements,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'total' => $playerAchievements->count(),
            'catalog_imported' => $catalogImported,
            'game_name' => $response->game_name,
        ];
    }
}
