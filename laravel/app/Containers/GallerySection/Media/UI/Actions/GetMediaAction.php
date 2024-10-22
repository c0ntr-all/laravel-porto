<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\UI\Actions;

use App\Containers\GallerySection\Media\Models\Media;
use App\Containers\GallerySection\Media\UI\API\Transformers\MediaTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetMediaAction
{
    use AsAction;

    public function handle(Media $media): Media
    {
        return $media;
    }

    public function asController(Media $media): JsonResponse
    {
        $media = $this->handle($media);

        return fractal($media, new MediaTransformer())
            ->withResourceName('media')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
