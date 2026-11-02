<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\UI\Actions;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Tasks\ListPlayerAchievementsForGameTask;
use App\Containers\GameSection\PlayerAchievement\UI\API\Requests\IndexRequest;
use App\Containers\GameSection\PlayerAchievement\UI\API\Transformers\PlayerAchievementTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListPlayerAchievementsAction extends BaseAction
{
    public function __construct(
        private readonly ListPlayerAchievementsForGameTask $listPlayerAchievementsForGameTask,
    ) {}

    public function handle(Game $game, int $userId): Collection
    {
        return $this->listPlayerAchievementsForGameTask->run($game, $userId);
    }

    public function asController(Game $game, IndexRequest $request): JsonResponse
    {
        $items = $this->handle($game, (int) $request->user()->id);

        return fractal($items, new PlayerAchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_PLAYER_ACHIEVEMENT->value)
            ->addMeta(['count' => $items->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
