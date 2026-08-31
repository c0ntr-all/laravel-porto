<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\UI\API\Requests\GetImageRequest;
use App\Containers\GallerySection\Image\UI\API\Transformers\ImageTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetImageAction extends BaseAction
{
    public function handle(Image $image): Image
    {
        return $image;
    }

    public function asController(Image $image, GetImageRequest $request): JsonResponse
    {
        $image = $this->handle($image);

        return fractal($image, new ImageTransformer())
            ->withResourceName('images')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
