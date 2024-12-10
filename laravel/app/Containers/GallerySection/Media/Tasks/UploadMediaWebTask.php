<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Media\Tasks;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Media\Data\DTO\CreateMediaDto;
use App\Containers\GallerySection\Media\Data\DTO\UploadMediaFromWebDto;
use App\Containers\GallerySection\Media\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Media\Enums\MediaSourceEnum;
use App\Containers\GallerySection\Media\Factories\ImageSourceFactory;
use App\Containers\GallerySection\Media\Models\Media;
use App\Containers\GallerySection\Media\Services\PathGenerationService;
use App\Ship\Parents\Tasks\Task;

class UploadMediaWebTask extends Task
{
    private const string SOURCE_TYPE = MediaSourceEnum::WEB->value;

    public function __construct(
        private readonly CreateMediaInAlbumTask $createMediaInAlbumTask,
        private readonly CreateAllImageThumbsTask $createAllImageThumbsTask,
        private readonly PathGenerationService $pathGenerationService
    )
    {
    }

    public function run(Album $album, UploadMediaFromWebDto $uploadMediaDto): Media
    {
        $imageStrategy = ImageSourceFactory::create($uploadMediaDto->link, self::SOURCE_TYPE);
        $albumPath = $this->pathGenerationService->getAlbumFolderPath($uploadMediaDto->user_id, $album->id);
        $thumbnails = $this->createAllImageThumbsTask->run($imageStrategy, $albumPath);

        $createMediaDto = CreateMediaDto::from([
            'user_id' => $uploadMediaDto->user_id,
            'type' => 'image',
            'original_path' => $uploadMediaDto->link,
            'list_thumb_path' => $thumbnails[ImageThumbTypeEnum::LIST->value],
            'preview_thumb_path' => $thumbnails[ImageThumbTypeEnum::PREVIEW->value],
            'width' => $imageStrategy->getImage()->getWidth(),
            'height' => $imageStrategy->getImage()->getHeight(),
            'source' => self::SOURCE_TYPE
        ]);

        return $this->createMediaInAlbumTask->run($album, $createMediaDto);
    }
}
