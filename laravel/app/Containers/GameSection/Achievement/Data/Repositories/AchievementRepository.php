<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Data\Repositories;

use App\Containers\GameSection\Achievement\Data\DTO\AchievementCreateData;
use App\Containers\GameSection\Achievement\Data\DTO\AchievementUpdateData;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Game\Models\Game;
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

    public function create(Game $game, AchievementCreateData $dto): Achievement
    {
        return Achievement::query()->create([
            'game_id' => $game->id,
            'name' => $dto->name,
            'description' => $dto->description,
            'steam_api_name' => $dto->steam_api_name,
        ]);
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
