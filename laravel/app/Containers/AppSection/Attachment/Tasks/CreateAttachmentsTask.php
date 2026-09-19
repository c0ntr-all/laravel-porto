<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tasks;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentCreateDto;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Model;

class CreateAttachmentsTask extends ParentTask
{
    public function __construct(
        private readonly CreateAttachmentTask $createAttachmentTask,
        private readonly ValidateFileableOwnershipTask $validateFileableOwnershipTask,
    ) {
    }

    /**
     * @param list<array{type: string, id: string}> $fileableReferences
     */
    public function run(
        Model $attachable,
        int $userId,
        string $attachableType,
        array $fileableReferences,
    ): void {
        foreach ($fileableReferences as $reference) {
            $fileableType = ContainerAliasEnum::toCanonicalMorphAlias($reference['type']);
            $fileableId = (string) $reference['id'];

            $fileable = $this->validateFileableOwnershipTask->run($fileableType, $fileableId, $userId);

            $this->createAttachmentTask->run(AttachmentCreateDto::from([
                'user_id' => (string) $userId,
                'attachable_type' => ContainerAliasEnum::toCanonicalMorphAlias($attachableType),
                'attachable_id' => (string) $attachable->getKey(),
                'fileable_type' => $fileableType,
                'fileable_id' => (string) $fileable->getKey(),
            ]));
        }
    }
}
