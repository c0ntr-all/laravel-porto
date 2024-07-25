<?php declare(strict_types=1);

namespace App\Containers\Music\UI\Actions\Tracks;

use App\Containers\Music\Data\Repositories\TrackRepository;
use App\Containers\Music\Models\Artist;
use App\Containers\Music\UI\API\Resources\Albums\Page\Tracks\TrackCollection;
use Illuminate\Http\Response;
use Illuminate\Pagination\CursorPaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListTracksByArtistAction
{
    use AsAction;

    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    public function handle(Artist $artist): CursorPaginator
    {
        $albumIds = $artist->albums()->pluck('id')->toArray();

        return $this->trackRepository->listTracksByAlbumIdsWithCursor($albumIds);
    }

    public function asController(Artist $artist): Response
    {
        $data = $this->handle($artist);

        return response(new TrackCollection($data));
    }
}
