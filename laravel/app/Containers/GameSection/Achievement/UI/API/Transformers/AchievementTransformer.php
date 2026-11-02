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
            'icon' => $achievement->icon,
            'icon_gray' => $achievement->icon_gray,
            'hidden' => $achievement->hidden,
            'player_percent_unlocked' => $achievement->player_percent_unlocked,
            'steam_internal_key' => $achievement->steam_internal_key,
            'min_progress' => $achievement->min_progress,
            'max_progress' => $achievement->max_progress,
            'group_id' => $achievement->group_id,
            'archived' => $achievement->archived,
            'progress_type' => $achievement->progress_type,
            'created_at' => $achievement->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $achievement->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
