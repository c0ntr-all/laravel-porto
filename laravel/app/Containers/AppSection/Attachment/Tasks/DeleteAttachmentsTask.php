<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tasks;

use App\Containers\AppSection\Attachment\Data\DTO\AttachmentsDeleteDto;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class DeleteAttachmentsTask extends ParentTask
{
    /**
     * @param Model $model
     * @param AttachmentsDeleteDto $dto
     * @return Collection
     */
    public function run(Model $model, AttachmentsDeleteDto $dto): Collection
    {
        $model->attachments()->whereIn('id', $dto->deleted_attachments_ids)->delete();

        return $model->attachments;
    }
}
