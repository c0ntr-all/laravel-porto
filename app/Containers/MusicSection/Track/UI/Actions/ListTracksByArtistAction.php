<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Album\UI\API\Resources\Page\Tracks\TrackCollection;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
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
