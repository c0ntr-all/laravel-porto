<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Tasks\ListFoldersTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\IndexRequest;
use App\Containers\MovieSection\Folder\UI\API\Transformers\FolderTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ListFoldersAction extends BaseAction
{
    public function __construct(
        private readonly ListFoldersTask $listFoldersTask,
    ) {
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $folders = $this->listFoldersTask->run((int) $request->user()->id);

        return fractal($folders, new FolderTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FOLDER->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
