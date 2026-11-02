<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\UI\API\Transformers;

use App\Containers\GameSection\Achievement\UI\API\Transformers\AchievementTransformer;
use App\Containers\GameSection\PlayerAchievement\Models\PlayerAchievement;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class PlayerAchievementTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'achievement',
    ];

    protected array $defaultIncludes = [
        'achievement',
    ];

    public function transform(PlayerAchievement $playerAchievement): array
    {
        return [
            'id' => $playerAchievement->id,
            'user_id' => $playerAchievement->user_id,
            'achievement_id' => $playerAchievement->achievement_id,
            'achieved' => $playerAchievement->achieved,
            'unlock_time' => $playerAchievement->unlock_time?->format('Y-m-d H:i:s'),
            'created_at' => $playerAchievement->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $playerAchievement->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeAchievement(PlayerAchievement $playerAchievement): Item|NullResource
    {
        if ($playerAchievement->achievement === null) {
            return $this->null();
        }

        return $this->item(
            $playerAchievement->achievement,
            new AchievementTransformer,
            ContainerAliasEnum::GAME_ACHIEVEMENT->value,
        );
    }
}
