<?php

namespace App\Containers\Music\UI\Actions\Albums;

use App\Containers\Music\Data\Repositories\AlbumRepository;
use App\Containers\Music\Models\Artist;
use App\Containers\Music\UI\API\Resources\Albums\ForArtistPage\AlbumCollection;
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
