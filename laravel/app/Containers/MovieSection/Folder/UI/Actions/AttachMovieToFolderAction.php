<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Tasks\AttachMovieToFolderTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\AttachMovieRequest;
use App\Containers\MovieSection\Folder\UI\API\Transformers\FolderTransformer;
use App\Containers\MovieSection\Movie\Tasks\FindMovieByIdTask;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class AttachMovieToFolderAction extends BaseAction
{
    public function __construct(
        private readonly AttachMovieToFolderTask $attachMovieToFolderTask,
        private readonly FindMovieByIdTask $findMovieByIdTask,
    ) {
    }

    public function asController(Folder $folder, AttachMovieRequest $request): JsonResponse
    {
        abort_unless((int) $folder->user_id === (int) $request->user()->id, 404);

        $movie = $this->findMovieByIdTask->run((int) $request->validated('movie_id'));
        $folder = $this->attachMovieToFolderTask->run($folder, $movie);

        return fractal($folder, new FolderTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FOLDER->value)
            ->addMeta(['message' => 'Movie added to folder successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
