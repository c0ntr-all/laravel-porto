<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\API\Transformers;

use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Ship\Enums\ContainerAliasEnum;
use League\Fractal\TransformerAbstract;

class CustomFieldTransformer extends TransformerAbstract
{
    public function transform(CustomField $customField): array
    {
        return [
            'id' => (string) $customField->id,
            'type' => $customField->type->value,
            'payload' => $customField->payload,
            'position' => (int) $customField->position,
            'fieldable_id' => (string) $customField->fieldable_id,
            'fieldable_type' => ContainerAliasEnum::toCanonicalMorphAlias((string) $customField->fieldable_type),
            'created_at' => $customField->created_at?->format('Y-m-d H:i:s'),
            'updated_at' => $customField->updated_at?->format('Y-m-d H:i:s'),
        ];
    }
}
