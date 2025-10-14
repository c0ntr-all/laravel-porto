<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tasks;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentCreateDto;
use App\Containers\AppSection\Attachment\Data\Repositories\AttachmentRepository;
use App\Containers\AppSection\Attachment\Models\Attachment;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateAttachmentTask extends ParentTask
{
    public function __construct(
        private readonly AttachmentRepository $AttachmentRepository
    )
    {
    }

    /**
     * @param AttachmentCreateDto $dto
     * @return Attachment
     */
    public function run(AttachmentCreateDto $dto): Attachment
    {
        return $this->AttachmentRepository->create($dto->toArray());
    }
}
