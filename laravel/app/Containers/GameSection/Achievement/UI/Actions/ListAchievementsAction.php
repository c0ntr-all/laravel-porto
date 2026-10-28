<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\Actions;

use App\Containers\GameSection\Achievement\Tasks\ListAchievementsTask;
use App\Containers\GameSection\Achievement\UI\API\Requests\IndexRequest;
use App\Containers\GameSection\Achievement\UI\API\Transformers\AchievementTransformer;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListAchievementsAction extends BaseAction
{
    public function __construct(
        private readonly ListAchievementsTask $listAchievementsTask,
    ) {}

    public function handle(Game $game): Collection
    {
        return $this->listAchievementsTask->run($game);
    }

    public function asController(Game $game, IndexRequest $request): JsonResponse
    {
        $achievements = $this->handle($game);

        return fractal($achievements, new AchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_ACHIEVEMENT->value)
            ->addMeta(['count' => $achievements->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
