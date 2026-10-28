<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\Actions;

use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Achievement\UI\API\Requests\GetRequest;
use App\Containers\GameSection\Achievement\UI\API\Transformers\AchievementTransformer;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetAchievementAction extends BaseAction
{
    public function handle(Achievement $achievement): Achievement
    {
        return $achievement;
    }

    public function asController(Game $game, Achievement $achievement, GetRequest $request): JsonResponse
    {
        $achievement = $this->handle($achievement);

        return fractal($achievement, new AchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_ACHIEVEMENT->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
