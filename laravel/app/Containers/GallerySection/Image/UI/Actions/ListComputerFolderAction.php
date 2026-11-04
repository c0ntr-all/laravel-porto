<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Tasks\ListComputerFolderTask;
use App\Containers\GallerySection\Image\UI\API\Requests\ListComputerFolderRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class ListComputerFolderAction extends BaseAction
{
    public function __construct(
        private readonly ListComputerFolderTask $listComputerFolderTask,
    ) {
    }

    public function asController(ListComputerFolderRequest $request): JsonResponse
    {
        $listing = $this->listComputerFolderTask->run(
            $request->windowsPath(),
            (int) auth()->id(),
            $request->page(),
            $request->perPage(),
        );

        $lastPage = max(1, (int) ceil($listing['total'] / $listing['per_page']));

        return response()->json([
            'data' => [
                'path' => $listing['path'],
                'name' => $listing['name'],
                'breadcrumbs' => $listing['breadcrumbs'],
                'folders' => $listing['folders'],
                'files' => $listing['files'],
            ],
            'meta' => [
                'page' => $listing['page'],
                'per_page' => $listing['per_page'],
                'total' => $listing['total'],
                'last_page' => $lastPage,
            ],
        ]);
    }
}
