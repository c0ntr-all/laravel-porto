<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Support;

use App\Containers\GameSection\Game\Models\Game;

class GameStateSnapshot
{
    /**
     * @return array{
     *     id: int,
     *     steam_id: int|null,
     *     name: string,
     *     description: string|null,
     *     img: string|null,
     *     air_date: string|null,
     *     developer: string|null,
     *     publisher: string|null
     * }
     */
    public static function fromGame(Game $game): array
    {
        return [
            'id' => (int) $game->id,
            'steam_id' => $game->steam_id !== null ? (int) $game->steam_id : null,
            'name' => $game->name,
            'description' => $game->description,
            'img' => $game->img,
            'air_date' => $game->air_date?->format('Y-m-d'),
            'developer' => $game->developer,
            'publisher' => $game->publisher,
        ];
    }

    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>  $after
     * @return array<string, array{old: mixed, new: mixed}>
     */
    public static function diff(?array $before, array $after): array
    {
        if ($before === null) {
            return [];
        }

        $changes = [];
        foreach (['name', 'description', 'img', 'air_date', 'developer', 'publisher'] as $key) {
            $old = $before[$key] ?? null;
            $new = $after[$key] ?? null;
            if ($old === $new) {
                continue;
            }

            $changes[$key] = [
                'old' => $old,
                'new' => $new,
            ];
        }

        return $changes;
    }
}
