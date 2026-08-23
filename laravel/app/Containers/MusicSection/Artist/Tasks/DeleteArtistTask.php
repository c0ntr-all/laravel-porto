<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteArtistTask extends ParentTask
{
    public function __construct(
        private readonly ArtistRepository $artistRepository
    )
    {
    }

    public function run(Artist $artist): ?bool
    {
        return $this->artistRepository->delete($artist);
    }
}
