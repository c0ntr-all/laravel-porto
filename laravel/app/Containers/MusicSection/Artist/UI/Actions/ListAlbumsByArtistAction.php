<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Album\Tasks\ListAlbumsByArtistTask;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumInArtistTransformer;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\UI\API\Requests\GetRequest;
use Illuminate\Http\JsonResponse;
use App\Ship\Parents\Actions\BaseAction;

class ListAlbumsByArtistAction extends BaseAction
{

    public function __construct(
        private readonly ListAlbumsByArtistTask $listAlbumsByArtistTask
    )
    {
    }

    public function handle(Artist $artist)
    {
        return $this->listAlbumsByArtistTask->run($artist);
    }

    public function asController(Artist $artist, GetRequest $request): JsonResponse
    {
        $albums = $this->handle($artist);

        return fractal($albums, new AlbumInArtistTransformer())
            ->withResourceName('albums')
            ->addMeta(['albums_count' => $albums->count()])
            ->parseIncludes(['artists', 'tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
