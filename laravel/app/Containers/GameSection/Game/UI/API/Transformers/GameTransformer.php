<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\API\Transformers;

use App\Containers\GameSection\Game\Models\Game;
use League\Fractal\TransformerAbstract;

class GameTransformer extends TransformerAbstract
{
    public function transform(Game $game): array
    {
        return [
            'id' => $game->id,
            'name' => $game->name,
            'description' => $game->description,
            'img' => $game->img,
            'air_date' => $game->air_date?->format('Y-m-d'),
            'developer' => $game->developer,
            'publisher' => $game->publisher,
            'steam_id' => $game->steam_id,
            'created_at' => $game->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $game->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
