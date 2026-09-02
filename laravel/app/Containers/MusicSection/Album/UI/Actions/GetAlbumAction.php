<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\UI\API\Requests\GetRequest;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTransformer;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class GetAlbumAction extends BaseAction
{

    public function handle(Album $album): Album
    {
        return $album->load([
            'tracks.artists',
            'tracks.rate',
            'discs' => fn ($query) => $query->withCount('tracks'),
            'tags',
            'versions.albumType',
            'parent.albumType',
            'artists',
            'albumType',
        ]);
    }

    public function asController(Album $album, GetRequest $request): JsonResponse
    {
        $album = $this->handle($album);

        return fractal($album, new AlbumTransformer())
            ->withResourceName('albums')
            ->parseIncludes(['artists', 'tracks', 'tracks.artists', 'tags', 'versions', 'parent', 'discs'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
