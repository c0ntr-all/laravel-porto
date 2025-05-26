<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\Tasks;

use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;

class UpdateOrCreateArtistTask extends ParentTask
{
    public function __construct(
        private readonly ArtistRepository $repository,
        private readonly UploadArtistCoverTask $uploadArtistCoverTask
    )
    {
    }

    /**
     * @param CreateArtistDto $dto
     * @return Artist
     */
    public function run(CreateArtistDto $dto): Artist
    {
        $artist = $this->repository->updateOrCreate($dto);
        $artistId = (string)$artist->id;

        if ($dto->image) {
            $image = new File($dto->image);

            $dto->image = $this->uploadArtistCoverTask->run($image, $artistId);
        }

        $artist->image = $dto->image;
        $artist->save();

        return $artist;
    }
}
