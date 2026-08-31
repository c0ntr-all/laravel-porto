<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Tasks\ListImagesTask;
use App\Containers\GallerySection\Image\UI\API\Requests\ListImagesRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListImagesAction extends BaseAction
{
    public function __construct(
        private readonly ListImagesTask $listImagesTask
    ) {
    }

    public function handle(Album $album): Collection
    {
        return $this->listImagesTask->run($album);
    }

    public function asController(Album $album, ListImagesRequest $request): JsonResponse
    {
        $images = $this->handle($album);

        return fractal($images, new ImageTransformer())
            ->withResourceName('images')
            ->addMeta(['count' => $images->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
