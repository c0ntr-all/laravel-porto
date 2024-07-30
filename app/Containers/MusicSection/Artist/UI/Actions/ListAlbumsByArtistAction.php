<?php

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\UI\API\Resources\Page\Albums\AlbumCollection;
use Illuminate\Database\Eloquent\Collection;
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

    public function handle(Artist $artist): Collection
    {
        return $this->albumRepository->listAlbumsByArtist($artist);
    }

    public function asController(Artist $artist): Response
    {
        $data = $this->handle($artist);

        return response(new AlbumCollection($data));
    }
}
