<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Tasks\RelinkComputerImagesTask;
use App\Containers\GallerySection\Image\UI\API\Requests\RelinkComputerImagesRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class RelinkComputerImagesAction extends BaseAction
{
    public function __construct(
        private readonly RelinkComputerImagesTask $relinkComputerImagesTask,
    ) {
    }

    public function asController(RelinkComputerImagesRequest $request): JsonResponse
    {
        $result = $this->relinkComputerImagesTask->run(
            $request->windowsPath(),
            (int) auth()->id(),
        );

        return response()->json([
            'data' => $result,
            'meta' => [
                'message' => $result['relinked'] > 0
                    ? 'Missing files were matched again.'
                    : 'No files could be matched.',
            ],
        ]);
    }
}
