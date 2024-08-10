<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTransformer;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class GetAlbumAction
{
    use AsAction;

    public function handle(Album $album): Album
    {
        return $album->load(['tracks.artists', 'tags', 'versions']);
    }

    public function asController(Album $album): JsonResponse
    {
        $album = $this->handle($album);

        return fractal($album, new AlbumTransformer())
            ->withResourceName('albums')
            ->parseIncludes(['artists', 'tracks', 'tags', 'versions'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
