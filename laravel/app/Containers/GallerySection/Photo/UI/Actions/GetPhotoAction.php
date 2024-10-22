<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Photo\UI\Actions;

use App\Containers\GallerySection\Photo\Models\Photo;
use App\Containers\GallerySection\Photo\UI\API\Transformers\PhotoTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetPhotoAction
{
    use AsAction;

    public function handle(Photo $photo): Photo
    {
        return $photo;
    }

    public function asController(Photo $photo): JsonResponse
    {
        $photo = $this->handle($photo);

        return fractal($photo, new PhotoTransformer())
            ->withResourceName('photos')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
