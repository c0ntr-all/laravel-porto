<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Data\DTO\FolderUpdateData;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Tasks\UpdateFolderTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\UpdateRequest;
use App\Containers\MovieSection\Folder\UI\API\Transformers\FolderTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateFolderAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::MOVIE_FOLDER;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateFolderTask $updateFolderTask,
    ) {
        parent::__construct();
    }

    public function asController(Folder $folder, UpdateRequest $request): JsonResponse
    {
        abort_unless((int) $folder->user_id === (int) $request->user()->id, 404);

        $folder = $this->updateFolderTask->run(
            $folder,
            FolderUpdateData::from($request->validated()),
        );

        $this->recordUseCase($folder);

        return fractal($folder, new FolderTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FOLDER->value)
            ->addMeta(['message' => 'Folder updated successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
