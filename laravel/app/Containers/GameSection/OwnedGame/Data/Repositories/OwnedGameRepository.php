<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\Data\Repositories;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\OwnedGame\Models\OwnedGame;
use App\Containers\GameSection\Steam\Data\DTO\SteamOwnedGameDto;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;

class OwnedGameRepository
{
    public function getForUser(int $userId): Collection
    {
        return QueryBuilder::for(
            OwnedGame::query()
                ->where('user_id', $userId)
                ->with('game'),
        )
            ->allowedFilters([
                AllowedFilter::exact('game_id'),
            ])
            ->allowedSorts(['playtime_forever', 'last_played_at', 'created_at'])
            ->defaultSort('-playtime_forever')
            ->get();
    }

    /**
     * @return Collection<int, Game>
     */
    public function mapCatalogGamesBySteamId(): Collection
    {
        return Game::query()
            ->whereNotNull('steam_id')
            ->get()
            ->keyBy('steam_id');
    }

    /**
     * @return array{owned_game: OwnedGame, was_created: bool}
     */
    public function upsertFromSteam(int $userId, Game $game, SteamOwnedGameDto $dto): array
    {
        $lastPlayedAt = null;
        if ($dto->rtime_last_played !== null && $dto->rtime_last_played > 0) {
            $lastPlayedAt = Carbon::createFromTimestampUTC($dto->rtime_last_played)
                ->timezone((string) config('app.timezone'))
                ->toDateTimeString();
        }

        $ownedGame = OwnedGame::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'game_id' => $game->id,
            ],
            [
                'playtime_forever' => max(0, $dto->playtime_forever),
                'last_played_at' => $lastPlayedAt,
            ],
        );

        return [
            'owned_game' => $ownedGame,
            'was_created' => $ownedGame->wasRecentlyCreated,
        ];
    }
}
