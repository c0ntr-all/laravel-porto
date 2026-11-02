<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Data\Repositories;

use App\Containers\GameSection\Achievement\Data\DTO\AchievementCreateData;
use App\Containers\GameSection\Achievement\Data\DTO\AchievementUpdateData;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Steam\Data\DTO\SteamGameAchievementDto;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;

class AchievementRepository
{
    public function getForGame(Game $game): Collection
    {
        return QueryBuilder::for(Achievement::query()->where('game_id', $game->id))
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::exact('steam_api_name'),
            ])
            ->allowedSorts(['name', 'created_at'])
            ->defaultSort('name')
            ->get();
    }

    public function countForGame(Game $game): int
    {
        return Achievement::query()->where('game_id', $game->id)->count();
    }

    /**
     * @return \Illuminate\Support\Collection<string, Achievement>
     */
    public function mapBySteamApiName(Game $game): \Illuminate\Support\Collection
    {
        return Achievement::query()
            ->where('game_id', $game->id)
            ->get()
            ->keyBy('steam_api_name');
    }

    public function create(Game $game, AchievementCreateData $dto): Achievement
    {
        return Achievement::query()->create([
            'game_id' => $game->id,
            'name' => $dto->name,
            'description' => $dto->description,
            'steam_api_name' => $dto->steam_api_name,
        ]);
    }

    /**
     * @return array{achievement: Achievement, was_created: bool}
     */
    public function upsertFromSteam(Game $game, SteamGameAchievementDto $dto): array
    {
        $achievement = Achievement::query()->updateOrCreate(
            [
                'game_id' => $game->id,
                'steam_api_name' => $dto->internal_name,
            ],
            [
                'name' => $dto->localized_name,
                'description' => $dto->localized_desc,
                'icon' => $dto->icon,
                'icon_gray' => $dto->icon_gray,
                'hidden' => $dto->hidden,
                'player_percent_unlocked' => $dto->player_percent_unlocked,
                'steam_internal_key' => $dto->internal_key,
                'min_progress' => $dto->min_progress_int,
                'max_progress' => $dto->max_progress_int,
                'group_id' => $dto->groupid,
                'archived' => $dto->archived,
                'progress_type' => $dto->progress_type,
            ],
        );

        return [
            'achievement' => $achievement,
            'was_created' => $achievement->wasRecentlyCreated,
        ];
    }

    public function update(Achievement $achievement, AchievementUpdateData $dto): Achievement
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $achievement->update($attributes);
        }

        return $achievement->refresh();
    }

    public function delete(Achievement $achievement): bool
    {
        return (bool) $achievement->delete();
    }
}
