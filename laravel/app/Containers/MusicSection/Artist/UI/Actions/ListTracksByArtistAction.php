<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Data\Collections\TrackCollection;
use App\Containers\MusicSection\Track\Tasks\ListTracksByArtistTask;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

final class ListTracksByArtistAction
{
    use AsAction;

    public function __construct(
        private readonly ListTracksByArtistTask $listTracksByArtistTask
    )
    {
    }

    public function handle(Artist $artist): CursorPaginator
    {
        return $this->listTracksByArtistTask->run($artist);
    }

    public function asController(Artist $artist): JsonResponse
    {
        $tracks = $this->handle($artist);

        return fractal($tracks, new TrackTransformer())
            ->withResourceName('tracks')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
