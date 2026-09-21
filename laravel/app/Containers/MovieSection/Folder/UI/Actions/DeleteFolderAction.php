<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Tasks\DeleteFolderTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteFolderAction extends BaseAction
{
    public function __construct(
        private readonly DeleteFolderTask $deleteFolderTask,
    ) {
    }

    public function asController(Folder $folder, DeleteRequest $request): JsonResponse
    {
        abort_unless((int) $folder->user_id === (int) $request->user()->id, 404);

        $this->deleteFolderTask->run($folder);

        return response()->json([
            'meta' => [
                'message' => 'Folder successfully deleted!',
            ],
        ]);
    }
}
