<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Uploaders;

use App\Containers\AppSection\Attachment\Contracts\FileableUploaderInterface;
use App\Containers\AppSection\Attachment\Data\DTO\FileableReferenceDto;
use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask;
use App\Containers\GallerySection\Video\Data\DTO\UploadVideoFromDeviceDto;
use App\Containers\GallerySection\Video\UI\Actions\UploadVideoFromDeviceAction;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Http\UploadedFile;

class VideoFileableUploader implements FileableUploaderInterface
{
    public function __construct(
        private readonly GetSystemAlbumTask $getSystemAlbumTask,
        private readonly UploadVideoFromDeviceAction $uploadVideoFromDeviceAction,
    ) {
    }

    public function supports(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'video/');
    }

    public function upload(UploadedFile $file, int $userId): FileableReferenceDto
    {
        $album = $this->getSystemAlbumTask->run(SystemAlbumsEnum::UPLOAD->value);
        $video = $this->uploadVideoFromDeviceAction->handle(
            $album,
            UploadVideoFromDeviceDto::from([
                'user_id' => $userId,
                'file' => $file,
            ])
        );

        return FileableReferenceDto::from([
            'fileable_type' => ContainerAliasEnum::GALLERY_VIDEO->value,
            'fileable_id' => (string) $video->id,
        ]);
    }
}
