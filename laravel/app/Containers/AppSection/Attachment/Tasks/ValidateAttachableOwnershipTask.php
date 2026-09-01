<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Tasks;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ValidateAttachableOwnershipTask extends ParentTask
{
    public function run(string $attachableType, string $attachableId, int $userId): Model
    {
        $canonicalType = ContainerAliasEnum::toCanonicalMorphAlias($attachableType);
        $modelClass = Relation::getMorphedModel($canonicalType);

        if ($modelClass === null) {
            throw new NotFoundHttpException('Unknown attachable type.');
        }

        /** @var Model|null $model */
        $model = $modelClass::query()->find($attachableId);

        if ($model === null) {
            throw new NotFoundHttpException('Attachable entity not found.');
        }

        if (!isset($model->user_id) || (int) $model->user_id !== $userId) {
            throw new AccessDeniedHttpException('Attachable entity does not belong to the current user.');
        }

        return $model;
    }
}
