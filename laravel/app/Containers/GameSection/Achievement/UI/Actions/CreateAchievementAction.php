<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\Actions;

use App\Containers\GameSection\Achievement\Data\DTO\AchievementCreateData;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Achievement\Tasks\CreateAchievementTask;
use App\Containers\GameSection\Achievement\UI\API\Requests\CreateRequest;
use App\Containers\GameSection\Achievement\UI\API\Transformers\AchievementTransformer;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateAchievementAction extends BaseAction
{
    public function __construct(
        private readonly CreateAchievementTask $createAchievementTask,
    ) {}

    public function handle(Game $game, AchievementCreateData $dto): Achievement
    {
        return $this->createAchievementTask->run($game, $dto);
    }

    public function asController(Game $game, CreateRequest $request): JsonResponse
    {
        $achievement = $this->handle($game, AchievementCreateData::from($request->validated()));

        return fractal($achievement, new AchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_ACHIEVEMENT->value)
            ->addMeta(['message' => 'Achievement created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
