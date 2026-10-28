<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\Actions;

use App\Containers\GameSection\Game\Data\DTO\GameUpdateData;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Game\Tasks\UpdateGameTask;
use App\Containers\GameSection\Game\UI\API\Requests\UpdateRequest;
use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateGameAction extends BaseAction
{
    public function __construct(
        private readonly UpdateGameTask $updateGameTask,
    ) {}

    public function handle(Game $game, GameUpdateData $dto): Game
    {
        return $this->updateGameTask->run($game, $dto);
    }

    public function asController(Game $game, UpdateRequest $request): JsonResponse
    {
        $game = $this->handle($game, GameUpdateData::from($request->validated()));

        return fractal($game, new GameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME->value)
            ->addMeta(['message' => 'Game updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
