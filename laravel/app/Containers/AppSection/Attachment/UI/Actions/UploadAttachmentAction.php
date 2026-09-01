<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\UI\Actions;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentCreateDto;
use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsCreateDto;
use App\Containers\AppSection\Attachment\Tasks\CreateAttachmentTask;
use App\Containers\AppSection\Attachment\Tasks\ResolveFileableFromUploadTask;
use App\Containers\AppSection\Attachment\Tasks\ValidateAttachableOwnershipTask;
use App\Containers\AppSection\Attachment\UI\API\Requests\UploadRequest;
use App\Containers\AppSection\Attachment\UI\API\Transformers\AttachmentTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class UploadAttachmentAction extends BaseAction
{
    public function __construct(
        private readonly CreateAttachmentTask $createAttachmentTask,
        private readonly ResolveFileableFromUploadTask $resolveFileableFromUploadTask,
        private readonly ValidateAttachableOwnershipTask $validateAttachableOwnershipTask,
    ) {
    }

    public function handle(AttachmentsCreateDto $attachmentsCreateDto): Collection
    {
        $userId = (int) $attachmentsCreateDto->user_id;
        $attachableType = ContainerAliasEnum::toCanonicalMorphAlias($attachmentsCreateDto->attachable_type);

        $this->validateAttachableOwnershipTask->run(
            $attachableType,
            $attachmentsCreateDto->attachable_id,
            $userId
        );

        $attachments = [];

        foreach ($attachmentsCreateDto->files as $file) {
            $fileableReference = $this->resolveFileableFromUploadTask->run($file, $userId);

            $attachment = $this->createAttachmentTask->run(AttachmentCreateDto::from([
                'user_id' => (string) $userId,
                'attachable_type' => $attachableType,
                'attachable_id' => (string) $attachmentsCreateDto->attachable_id,
                'fileable_type' => $fileableReference->fileable_type,
                'fileable_id' => $fileableReference->fileable_id,
            ]));

            $attachments[] = $attachment->load('fileable');
        }

        return collect($attachments);
    }

    public function asController(UploadRequest $request): JsonResponse
    {
        $requestData = $request->validated();

        $attachmentDto = AttachmentsCreateDto::from([
            'user_id' => (string) auth()->id(),
            ...$requestData,
        ]);

        $attachments = $this->handle($attachmentDto);

        return fractal($attachments, new AttachmentTransformer())
            ->withResourceName('attachments')
            ->addMeta(['message' => 'Attachments successfully created!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
