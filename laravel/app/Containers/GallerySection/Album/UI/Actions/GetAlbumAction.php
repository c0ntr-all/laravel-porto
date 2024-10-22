<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAlbumAction
{
    use AsAction;

    public function handle(Album $album): Album
    {
        return $album;
    }

    public function asController(Album $album): JsonResponse
    {
        $album = $this->handle($album);

        return fractal($album, new AlbumTransformer())
            ->withResourceName('albums')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
