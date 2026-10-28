<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\Actions;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Game\UI\API\Requests\GetRequest;
use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetGameAction extends BaseAction
{
    public function handle(Game $game): Game
    {
        return $game;
    }

    public function asController(Game $game, GetRequest $request): JsonResponse
    {
        $game = $this->handle($game);

        return fractal($game, new GameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
