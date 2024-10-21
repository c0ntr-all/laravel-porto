<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Tasks;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Http\File;

class UpdateOrCreateAlbumTask extends Task
{
    public function __construct(
        private readonly AlbumRepository $albumRepository,
        private readonly UploadAlbumCoverTask $uploadAlbumCoverTask,
    ) {
    }

    public function run(Artist $artist, CreateAlbumDto $dto): Album
    {
        if ($dto->image) {
            $image = new File($dto->image);

            $dto->image = $this->uploadAlbumCoverTask->run($image, $dto->name, $artist->id);
        }

        return $this->albumRepository->updateOrCreate($dto);
    }
}
