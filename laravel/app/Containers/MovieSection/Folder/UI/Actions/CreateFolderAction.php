<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Data\DTO\FolderCreateData;
use App\Containers\MovieSection\Folder\Tasks\CreateFolderTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\CreateRequest;
use App\Containers\MovieSection\Folder\UI\API\Transformers\FolderTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateFolderAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::MOVIE_FOLDER;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateFolderTask $createFolderTask,
    ) {
        parent::__construct();
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $folder = $this->createFolderTask->run(FolderCreateData::from([
            'user_id' => (int) $request->user()->id,
            'name' => $request->validated('name'),
        ]));

        $this->recordUseCase($folder);

        return fractal($folder, new FolderTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FOLDER->value)
            ->addMeta(['message' => 'Folder created successfully!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
