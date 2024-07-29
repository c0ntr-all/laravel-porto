<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\Providers\ArtistDependencyProvider;
use App\Containers\MusicSection\Track\Data\Collections\TrackCollection;
use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;

final class ListTracksByArtistAction
{
    use AsAction;

    public function __construct(
        private readonly ArtistDependencyProvider $artistDependencyProvider
    )
    {
    }

    public function handle(Artist $artist): TrackCollection
    {
        return $this->artistDependencyProvider->getTrackFacade()->listTracksByArtist($artist);
    }

    public function asController(Artist $artist): Response
    {
        $data = $this->handle($artist);

        return response(new TrackCollection($data));
    }
}
