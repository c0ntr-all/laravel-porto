<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\Actions;

use App\Containers\GameSection\Game\Data\DTO\GameCreateData;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Game\Tasks\CreateGameTask;
use App\Containers\GameSection\Game\UI\API\Requests\CreateRequest;
use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateGameAction extends BaseAction
{
    public function __construct(
        private readonly CreateGameTask $createGameTask,
    ) {}

    public function handle(GameCreateData $dto): Game
    {
        return $this->createGameTask->run($dto);
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $game = $this->handle(GameCreateData::from($request->validated()));

        return fractal($game, new GameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME->value)
            ->addMeta(['message' => 'Game created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
