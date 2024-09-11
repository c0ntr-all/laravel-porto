<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Album\Tasks\ListAlbumsByArtistTask;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumInArtistTransformer;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAlbumsByArtistAction
{
    use AsAction;

    public function __construct(
        private readonly ListAlbumsByArtistTask $listAlbumsByArtistTask
    )
    {
    }

    public function handle(Artist $artist): JsonResponse
    {
        $albums = $this->listAlbumsByArtistTask->run($artist);

        return fractal($albums, new AlbumInArtistTransformer())
            ->withResourceName('albums')
            ->addMeta(['albums_count' => $albums->count()])
            ->parseIncludes(['artists', 'tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
