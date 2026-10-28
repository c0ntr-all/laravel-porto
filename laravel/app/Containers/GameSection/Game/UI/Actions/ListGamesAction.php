<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\Actions;

use App\Containers\GameSection\Game\Tasks\ListGamesTask;
use App\Containers\GameSection\Game\UI\API\Requests\IndexRequest;
use App\Containers\GameSection\Game\UI\API\Transformers\GameTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListGamesAction extends BaseAction
{
    public function __construct(
        private readonly ListGamesTask $listGamesTask,
    ) {}

    public function handle(): Collection
    {
        return $this->listGamesTask->run();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $games = $this->handle();

        return fractal($games, new GameTransformer)
            ->withResourceName(ContainerAliasEnum::GAME->value)
            ->addMeta(['count' => $games->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
