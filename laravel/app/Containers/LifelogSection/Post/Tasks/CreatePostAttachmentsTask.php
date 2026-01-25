<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentCreateDto;
use App\Containers\AppSection\Attachment\Tasks\CreateAttachmentTask;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreatePostAttachmentsTask extends ParentTask
{
    public function __construct(
        private readonly CreateAttachmentTask $createAttachmentTask,
    )
    {
    }

    /**
     * @param Post $post
     * @param int $userId
     * @param array $attachments
     * @return void
     */
    public function run(Post $post, int $userId, array $attachments): void
    {
        collect($attachments)->each(function (array $attachment) use ($post, $userId): void {
            $dto = AttachmentCreateDto::from([
                'user_id' => $userId,
                'attachable_type' => ContainerAliasEnum::LL_POST->value,
                'attachable_id' => $post->id,
                'fileable_type' => $attachment['type'],
                'fileable_id' => $attachment['id'],
            ]);
            // TODO: cross-section dependency
            $this->createAttachmentTask->run($dto);
        });
    }
}
