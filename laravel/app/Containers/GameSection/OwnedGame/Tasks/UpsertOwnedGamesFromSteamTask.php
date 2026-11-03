<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\Tasks;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\OwnedGame\Data\Repositories\OwnedGameRepository;
use App\Containers\GameSection\OwnedGame\Exceptions\SteamUserNotLinkedException;
use App\Containers\GameSection\OwnedGame\Models\OwnedGame;
use App\Containers\GameSection\Steam\Tasks\FetchSteamOwnedGamesTask;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;

class UpsertOwnedGamesFromSteamTask extends ParentTask
{
    public function __construct(
        private readonly OwnedGameRepository $ownedGameRepository,
        private readonly FetchSteamOwnedGamesTask $fetchSteamOwnedGamesTask,
    ) {}

    /**
     * @return array{
     *     owned_games: Collection<int, OwnedGame>,
     *     created: int,
     *     updated: int,
     *     skipped: int,
     *     total: int,
     *     game_count: int
     * }
     */
    public function run(User $user): array
    {
        $steamUser = $user->steamUser;
        if ($steamUser === null) {
            throw new SteamUserNotLinkedException((int) $user->id);
        }

        $catalog = $this->ownedGameRepository->mapCatalogGamesBySteamId();
        $response = $this->fetchSteamOwnedGamesTask->run($steamUser->steam_id);

        $created = 0;
        $updated = 0;
        $skipped = 0;
        /** @var Collection<int, OwnedGame> $ownedGames */
        $ownedGames = (new OwnedGame)->newCollection();

        foreach ($response->games as $dto) {
            $game = $catalog->get($dto->appid);
            if ($game === null) {
                $skipped++;

                continue;
            }

            $result = $this->ownedGameRepository->upsertFromSteam(
                (int) $user->id,
                $game,
                $dto,
            );
            $ownedGames->push($result['owned_game']->loadMissing('game'));

            if ($result['was_created']) {
                $created++;
            } else {
                $updated++;
            }
        }

        return [
            'owned_games' => $ownedGames,
            'created' => $created,
            'updated' => $updated,
            'skipped' => $skipped,
            'total' => $ownedGames->count(),
            'game_count' => $response->game_count,
        ];
    }
}
