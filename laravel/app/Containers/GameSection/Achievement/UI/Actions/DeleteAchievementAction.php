<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\Actions;

use App\Containers\GameSection\Achievement\Models\Achievement;
use App\Containers\GameSection\Achievement\Tasks\DeleteAchievementTask;
use App\Containers\GameSection\Achievement\UI\API\Requests\DeleteRequest;
use App\Containers\GameSection\Game\Models\Game;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteAchievementAction extends BaseAction
{
    public function __construct(
        private readonly DeleteAchievementTask $deleteAchievementTask,
    ) {}

    public function handle(Achievement $achievement): bool
    {
        return $this->deleteAchievementTask->run($achievement);
    }

    public function asController(Game $game, Achievement $achievement, DeleteRequest $request): JsonResponse
    {
        $this->handle($achievement);

        return response()->json([
            'meta' => [
                'message' => 'Achievement successfully deleted!',
            ],
        ]);
    }
}
