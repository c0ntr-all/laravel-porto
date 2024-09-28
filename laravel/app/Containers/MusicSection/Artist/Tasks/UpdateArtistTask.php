<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Containers\MusicSection\Artist\Data\DTO\UpdateArtistDto;
use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateArtistTask extends ParentTask
{
    public function __construct(
        private readonly ArtistRepository $artistRepository
    )
    {
    }

    /**
     * @param Artist $artist
     * @param UpdateArtistDto $dto
     * @return Artist
     */
    public function run(Artist $artist, UpdateArtistDto $dto): Artist
    {
        return $this->artistRepository->update($artist, $dto);
    }
}
