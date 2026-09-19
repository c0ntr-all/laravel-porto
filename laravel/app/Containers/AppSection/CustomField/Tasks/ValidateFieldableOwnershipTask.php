<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class ValidateFieldableOwnershipTask extends ParentTask
{
    public function run(string $fieldableType, string $fieldableId, int $userId): Model
    {
        $canonicalType = ContainerAliasEnum::toCanonicalMorphAlias($fieldableType);

        if (!in_array($canonicalType, ContainerAliasEnum::customFieldableTypes(), true)) {
            throw new UnprocessableEntityHttpException('Disallowed fieldable type.');
        }

        $modelClass = Relation::getMorphedModel($canonicalType);

        if ($modelClass === null) {
            throw new NotFoundHttpException('Unknown fieldable type.');
        }

        /** @var Model|null $model */
        $query = $modelClass::query();
        $model = in_array(\App\Ship\Models\Traits\HasUuidV7::class, class_uses_recursive($modelClass), true)
            ? $query->whereIdOrUuid($fieldableId)->first()
            : $query->find($fieldableId);

        if ($model === null) {
            throw new NotFoundHttpException('Fieldable entity not found.');
        }

        if (!method_exists($model, 'customFields')) {
            throw new UnprocessableEntityHttpException('Entity does not support custom fields.');
        }

        if (!isset($model->user_id) || (int) $model->user_id !== $userId) {
            throw new AccessDeniedHttpException('Fieldable entity does not belong to the current user.');
        }

        return $model;
    }
}
