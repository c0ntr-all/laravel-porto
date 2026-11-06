<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Tasks\DeleteFolderTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteFolderAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::MOVIE_FOLDER;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteFolderTask $deleteFolderTask,
    ) {
        parent::__construct();
    }

    public function asController(Folder $folder, DeleteRequest $request): JsonResponse
    {
        abort_unless((int) $folder->user_id === (int) $request->user()->id, 404);

        DB::transaction(function () use ($folder): void {
            $this->recordUseCase($folder);

            $this->deleteFolderTask->run($folder);
        });

        return response()->json([
            'meta' => [
                'message' => 'Folder successfully deleted!',
            ],
        ]);
    }
}
