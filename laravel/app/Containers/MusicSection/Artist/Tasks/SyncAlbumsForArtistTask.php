<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncAlbumsForArtistTask extends ParentTask
{
    public function __construct(
        private readonly ArtistRepository $artistRepository
    )
    {
    }

    /**
     * @param Artist $artist
     * @param array $albumsIds
     * @return array
     */
    public function run(Artist $artist, array $albumsIds): array
    {
        return $this->artistRepository->syncAlbumsWithoutDetaching($artist, $albumsIds);
    }
}
