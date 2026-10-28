<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Game\UI\Actions;

use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Game\Tasks\DeleteGameTask;
use App\Containers\GameSection\Game\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteGameAction extends BaseAction
{
    public function __construct(
        private readonly DeleteGameTask $deleteGameTask,
    ) {}

    public function handle(Game $game): bool
    {
        return $this->deleteGameTask->run($game);
    }

    public function asController(Game $game, DeleteRequest $request): JsonResponse
    {
        $this->handle($game);

        return response()->json([
            'meta' => [
                'message' => 'Game successfully deleted!',
            ],
        ]);
    }
}
