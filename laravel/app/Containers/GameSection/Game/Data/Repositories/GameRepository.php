<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\Data\Repositories;

use App\Containers\GameSection\Game\Data\DTO\GameCreateData;
use App\Containers\GameSection\Game\Data\DTO\GameUpdateData;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;
use Spatie\QueryBuilder\AllowedFilter;

class GameRepository
{
    public function get(): Collection
    {
        return QueryBuilder::for(Game::class)
            ->allowedFilters([
                AllowedFilter::partial('name'),
                AllowedFilter::partial('developer'),
                AllowedFilter::partial('publisher'),
                AllowedFilter::exact('steam_id'),
            ])
            ->allowedSorts(['name', 'air_date', 'created_at'])
            ->defaultSort('name')
            ->get();
    }

    public function create(GameCreateData $dto): Game
    {
        return Game::query()->create([
            'name' => $dto->name,
            'description' => $dto->description,
            'img' => $dto->img,
            'air_date' => $dto->air_date,
            'developer' => $dto->developer,
            'publisher' => $dto->publisher,
            'steam_id' => $dto->steam_id,
        ]);
    }

    public function update(Game $game, GameUpdateData $dto): Game
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $game->update($attributes);
        }

        return $game->refresh();
    }

    public function delete(Game $game): bool
    {
        return (bool) $game->delete();
    }

    public function upsertBySteamId(
        int $steamId,
        string $name,
        ?string $description,
        ?string $img,
        ?string $airDate,
        ?string $developer,
        ?string $publisher,
    ): Game {
        return Game::query()->updateOrCreate(
            ['steam_id' => $steamId],
            [
                'name' => $name,
                'description' => $description,
                'img' => $img,
                'air_date' => $airDate,
                'developer' => $developer,
                'publisher' => $publisher,
            ],
        );
    }
}
