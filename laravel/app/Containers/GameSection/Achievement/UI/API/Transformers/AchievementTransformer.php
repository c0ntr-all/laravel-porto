<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\API\Transformers;

use App\Containers\GameSection\Achievement\Models\Achievement;
use League\Fractal\TransformerAbstract;

class AchievementTransformer extends TransformerAbstract
{
    public function transform(Achievement $achievement): array
    {
        return [
            'id' => $achievement->id,
            'game_id' => $achievement->game_id,
            'name' => $achievement->name,
            'description' => $achievement->description,
            'steam_api_name' => $achievement->steam_api_name,
            'created_at' => $achievement->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $achievement->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
