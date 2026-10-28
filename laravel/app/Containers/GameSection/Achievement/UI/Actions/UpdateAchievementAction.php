<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\Actions;

use App\Containers\GameSection\Achievement\Data\DTO\AchievementUpdateData;
use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Achievement\Tasks\UpdateAchievementTask;
use App\Containers\GameSection\Achievement\UI\API\Requests\UpdateRequest;
use App\Containers\GameSection\Achievement\UI\API\Transformers\AchievementTransformer;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UpdateAchievementAction extends BaseAction
{
    public function __construct(
        private readonly UpdateAchievementTask $updateAchievementTask,
    ) {}

    public function handle(Achievement $achievement, AchievementUpdateData $dto): Achievement
    {
        return $this->updateAchievementTask->run($achievement, $dto);
    }

    public function asController(Game $game, Achievement $achievement, UpdateRequest $request): JsonResponse
    {
        $achievement = $this->handle($achievement, AchievementUpdateData::from($request->validated()));

        return fractal($achievement, new AchievementTransformer)
            ->withResourceName(ContainerAliasEnum::GAME_ACHIEVEMENT->value)
            ->addMeta(['message' => 'Achievement updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
