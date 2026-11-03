<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\UI\API\Transformers;

use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Containers\GameSection\OwnedGame\Models\OwnedGame;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\Resource\Item;
use League\Fractal\Resource\NullResource;
use League\Fractal\TransformerAbstract;

class OwnedGameTransformer extends TransformerAbstract
{
    protected array $availableIncludes = [
        'game',
    ];

    protected array $defaultIncludes = [
        'game',
    ];

    public function transform(OwnedGame $ownedGame): array
    {
        return [
            'id' => $ownedGame->id,
            'user_id' => $ownedGame->user_id,
            'game_id' => $ownedGame->game_id,
            'playtime_forever' => $ownedGame->playtime_forever,
            'last_played_at' => $ownedGame->last_played_at?->format('Y-m-d H:i:s'),
            'created_at' => $ownedGame->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $ownedGame->updated_at?->format('Y-m-d H:i:s'),
        ];
    }

    public function includeGame(OwnedGame $ownedGame): Item|NullResource
    {
        if ($ownedGame->game === null) {
            return $this->null();
        }

        return $this->item(
            $ownedGame->game,
            new GameTransformer,
            ContainerAliasEnum::GAME->value,
        );
    }
}
