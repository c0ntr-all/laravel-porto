<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\Repositories\ImageRepository;
use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Ramsey\Uuid\Uuid;

class SaveImageToSaveAlbumTask extends ParentTask
{
    public function __construct(
        private readonly GetSystemAlbumTask $getSystemAlbumTask,
        private readonly ImageRepository $imageRepository,
        private readonly CreateImageInAlbumTask $createImageInAlbumTask,
        private readonly CopyImageFilesTask $copyImageFilesTask,
    ) {
    }

    public function run(Image $image, int $userId): Image
    {
        $saveAlbum = $this->getSystemAlbumTask->run(SystemAlbumsEnum::SAVE->value);
        $originId = $image->saved_from_id ?: (string) $image->id;

        $existing = $this->imageRepository->findSavedCopy($userId, $saveAlbum->id, $originId);
        if ($existing !== null) {
            return $existing;
        }

        $copy = $this->createImageInAlbumTask->run($saveAlbum, CreateImageDto::from([
            'id' => Uuid::uuid4()->toString(),
            'user_id' => $userId,
            'source' => $image->source,
            'width' => $image->width,
            'height' => $image->height,
            'extension' => $image->extension,
            'external_url' => $image->external_url,
            'description' => $image->description,
            'saved_from_id' => $originId,
        ]));

        $this->copyImageFilesTask->run($image, $copy);

        return $copy;
    }
}
