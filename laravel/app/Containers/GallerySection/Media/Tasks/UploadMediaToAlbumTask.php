<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaDto;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Support\Collection;

class UploadMediaToAlbumTask extends Task
{
    public function __construct(
        private readonly CreateMediaInAlbumTask $createMediaInAlbumTask,
        private readonly DefineMediaTypeTask    $defineMediaTypeTask
    )
    {
    }

    //todo: It might be better to insert all the entries at once by raw query
    public function run(Album $album, UploadMediaDto $uploadMediaDto): Collection
    {
        $windowsImagesRootFolder = config('app.windows_images_root_folder');

        $result = collect();
        foreach($uploadMediaDto->media_paths as $path) {
            $createMediaDto = CreateMediaDto::from([
                'user_id' => $uploadMediaDto->user_id,
                'type' => $this->defineMediaTypeTask->run($path),
                'path' => str_replace($windowsImagesRootFolder, '', $path),
                'is_web' => false
            ]);

            $savedMedia = $this->createMediaInAlbumTask->run($album, $createMediaDto);

            $result->push($savedMedia);
        }

        return $result;
    }
}
