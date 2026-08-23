<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\Actions;

use App\Containers\MusicSection\History\Models\History;
use App\Containers\MusicSection\History\Tasks\DeleteHistoryTask;
use App\Containers\MusicSection\History\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteHistoryAction extends BaseAction
{
    public function __construct(
        private readonly DeleteHistoryTask $deleteHistoryTask
    )
    {
    }

    public function handle(History $history): ?bool
    {
        return $this->deleteHistoryTask->run($history);
    }

    public function asController(History $history, DeleteRequest $request): JsonResponse
    {
        $this->handle($history);

        return response()->json([
            'meta' => [
                'message' => 'History record successfully deleted!',
            ],
        ]);
    }
}
