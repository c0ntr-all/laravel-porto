<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\Actions;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Containers\MusicSection\Upload\Tasks\ListLibraryFoldersTask;
use App\Containers\MusicSection\Upload\UI\API\Requests\ListFoldersRequest;
use App\Containers\MusicSection\Upload\UI\API\Transformers\LibraryFolderTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ListLibraryFoldersAction extends BaseAction
{
    public function __construct(
        private readonly ListLibraryFoldersTask $listLibraryFoldersTask,
        private readonly ArtistRepository $artistRepository,
        private readonly AlbumRepository $albumRepository,
    ) {
    }

    public function asController(ListFoldersRequest $request): JsonResponse
    {
        $windowsPath = $request->windowsPath();
        $folders = $this->listLibraryFoldersTask->run($windowsPath);

        return fractal($folders, new LibraryFolderTransformer())
            ->withResourceName('folders')
            ->addMeta([
                'path' => $windowsPath,
                'name' => PathHelper::basename($windowsPath),
                'uploaded' => $this->artistRepository->findByPath($windowsPath) !== null
                    || $this->albumRepository->findByPath($windowsPath) !== null,
            ])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
