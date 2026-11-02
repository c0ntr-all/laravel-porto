<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Data\Repositories;

use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement;
use App\Containers\GameSection\Steam\Data\DTO\SteamPlayerAchievementDto;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Carbon;
use Spatie\QueryBuilder\AllowedFilter;

class PlayerAchievementRepository
{
    public function getForGameAndUser(Game $game, int $userId): Collection
    {
        return QueryBuilder::for(
            PlayerAchievement::query()
                ->where('user_id', $userId)
                ->whereHas('achievement', static function ($query) use ($game): void {
                    $query->where('game_id', $game->id);
                })
                ->with('achievement'),
        )
            ->allowedFilters([
                AllowedFilter::exact('achieved'),
                AllowedFilter::exact('achievement_id'),
            ])
            ->allowedSorts(['unlock_time', 'created_at', 'achieved'])
            ->defaultSort('-unlock_time')
            ->get();
    }

    /**
     * @return array{player_achievement: PlayerAchievement, was_created: bool}
     */
    public function upsertFromSteam(
        int $userId,
        Achievement $achievement,
        SteamPlayerAchievementDto $dto,
    ): array {
        $unlockTime = null;
        if ($dto->achieved && $dto->unlocktime !== null && $dto->unlocktime > 0) {
            $unlockTime = Carbon::createFromTimestampUTC($dto->unlocktime)
                ->timezone((string) config('app.timezone'))
                ->toDateTimeString();
        }

        $playerAchievement = PlayerAchievement::query()->updateOrCreate(
            [
                'user_id' => $userId,
                'achievement_id' => $achievement->id,
            ],
            [
                'achieved' => $dto->achieved,
                'unlock_time' => $unlockTime,
            ],
        );

        return [
            'player_achievement' => $playerAchievement,
            'was_created' => $playerAchievement->wasRecentlyCreated,
        ];
    }
}
