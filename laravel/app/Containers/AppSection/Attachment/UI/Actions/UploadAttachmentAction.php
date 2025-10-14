<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\UI\Actions;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentCreateDto;
use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsCreateDto;
use App\Containers\AppSection\Attachment\Tasks\CreateAttachmentTask;
use App\Containers\AppSection\Attachment\Tasks\StoreFileTask;
use App\Containers\AppSection\Attachment\UI\API\Requests\UploadRequest;
use App\Containers\AppSection\Attachment\UI\API\Transformers\AttachmentTransformer;
use App\Containers\GallerySection\Album\Enums\SystemAlbumsEnum;
use App\Containers\GallerySection\Album\Tasks\GetSystemAlbumTask;
use App\Containers\GallerySection\Image\Data\DTO\UploadImageFromDeviceDto;
use App\Containers\GallerySection\Image\UI\Actions\UploadImageFromDeviceAction;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class UploadAttachmentAction
{
    use AsAction;

    public function __construct(
        private readonly StoreFileTask $storeFileTask,
        private readonly CreateAttachmentTask $createAttachmentTask,
        private readonly GetSystemAlbumTask $getSystemAlbumTask,
        private readonly UploadImageFromDeviceAction $uploadImageFromDeviceAction
    )
    {
    }

    public function handle(AttachmentsCreateDto $attachmentsCreateDto)
    {
        $uploadsGalleryAlbum = $this->getSystemAlbumTask->run(SystemAlbumsEnum::UPLOAD->value);
        $attachments = [];

        foreach ($attachmentsCreateDto->files as $file) {
            $uploadImageDto = UploadImageFromDeviceDto::from([
                'user_id' => $attachmentsCreateDto->user_id,
                'file' => $file
            ]);

            $image = $this->uploadImageFromDeviceAction->handle($uploadsGalleryAlbum, $uploadImageDto);

            $attachmentCreateDto = AttachmentCreateDto::from([
                ...$attachmentsCreateDto->toArray(),
                'fileable_type' => ContainerAliasEnum::GALLERY_IMAGE->value,
                'fileable_id' => $image->id,
            ]);

            $attachment = $this->createAttachmentTask->run($attachmentCreateDto);

            $attachments[] = $attachment;
        }

        return collect($attachments);
    }

    public function asController(UploadRequest $request): JsonResponse
    {
        $attachmentDto = AttachmentsCreateDto::from([
            'user_id' => auth()->user()->id,
            ...$request->validated()
        ]);

        $attachments = $this->handle($attachmentDto);

        return fractal($attachments, new AttachmentTransformer())
            ->withResourceName('attachments')
            ->addMeta(['message' => 'Attachments successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
