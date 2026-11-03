<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\UI\Actions;

use App\Containers\GameSection\OwnedGame\Tasks\ListOwnedGamesForUserTask;
use App\Containers\GameSection\OwnedGame\UI\API\Requests\IndexRequest;
use App\Containers\GameSection\OwnedGame\UI\API\Transformers\OwnedGameTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListOwnedGamesAction extends BaseAction
{
    public function __construct(
        private readonly ListOwnedGamesForUserTask $listOwnedGamesForUserTask,
    ) {}

    public function handle(int $userId): Collection
    {
        return $this->listOwnedGamesForUserTask->run($userId);
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $items = $this->handle((int) $request->user()->id);

        return fractal($items, new OwnedGameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_OWNED_GAME->value)
            ->addMeta(['count' => $items->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
