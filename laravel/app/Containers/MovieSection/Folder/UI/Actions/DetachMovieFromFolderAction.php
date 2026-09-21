<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Tasks\DetachMovieFromFolderTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\DetachMovieRequest;
use App\Containers\MovieSection\Folder\UI\API\Transformers\FolderTransformer;
use App\Containers\MovieSection\Movie\Models\Movie;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DetachMovieFromFolderAction extends BaseAction
{
    public function __construct(
        private readonly DetachMovieFromFolderTask $detachMovieFromFolderTask,
    ) {
    }

    public function asController(Folder $folder, Movie $movie, DetachMovieRequest $request): JsonResponse
    {
        abort_unless((int) $folder->user_id === (int) $request->user()->id, 404);

        $folder = $this->detachMovieFromFolderTask->run($folder, $movie);

        return fractal($folder, new FolderTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE_FOLDER->value)
            ->addMeta(['message' => 'Movie removed from folder successfully!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
