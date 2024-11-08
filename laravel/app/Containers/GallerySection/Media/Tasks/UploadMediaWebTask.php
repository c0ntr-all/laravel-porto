<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaFromWebDto;
use App\Containers\GallerySection\Media\Models\Media;
use App\Ship\Parents\Tasks\Task;

class UploadMediaWebTask extends Task
{
    public function __construct(
        private readonly CreateMediaInAlbumTask $createMediaInAlbumTask
    )
    {
    }

    public function run(Album $album, UploadMediaFromWebDto $uploadMediaDto): Media
    {
        $createMediaDto = CreateMediaDto::from([
            'user_id' => $uploadMediaDto->user_id,
            //todo: Define media type from web
            'type' => 'image',
            'path' => $uploadMediaDto->link,
            'source' => 'web'
        ]);

        return $this->createMediaInAlbumTask->run($album, $createMediaDto);
    }
}
