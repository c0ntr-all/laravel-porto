<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Uploaders;

use App\Containers\AppSection\Attachment\Contracts\FileableUploaderInterface;
use App\Containers\AppSection\Attachment\Data\DTO\FileableReferenceDto;
use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromDeviceDto;
use App\Containers\GallerySection\Image\UI\Actions\UploadImageFromDeviceAction;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Http\UploadedFile;

class ImageFileableUploader implements FileableUploaderInterface
{
    public function __construct(
        private readonly GetSystemAlbumTask $getSystemAlbumTask,
        private readonly UploadImageFromDeviceAction $uploadImageFromDeviceAction,
    ) {
    }

    public function supports(string $mimeType): bool
    {
        return str_starts_with($mimeType, 'image/');
    }

    public function upload(UploadedFile $file, int $userId): FileableReferenceDto
    {
        $album = $this->getSystemAlbumTask->run(SystemAlbumsEnum::UPLOAD->value);
        $image = $this->uploadImageFromDeviceAction->handle(
            $album,
            UploadImageFromDeviceDto::from([
                'user_id' => $userId,
                'file' => $file,
            ])
        );

        return FileableReferenceDto::from([
            'fileable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
            'fileable_id' => (string) $image->id,
        ]);
    }
}
