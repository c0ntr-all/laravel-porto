<?php

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\UI\API\Resources\ForArtistPage\AlbumCollection;
use App\Containers\MusicSection\Artist\Models\Artist;
use Illuminate\Http\Response;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAlbumsByArtistAction
{
    use AsAction;

    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function handle(Artist $artist)
    {
        return $this->albumRepository->listAlbumsByArtist($artist);
    }

    public function asController(Artist $artist): Response
    {
        $data = $this->handle($artist);

        return response(new AlbumCollection($data));
    }
}
