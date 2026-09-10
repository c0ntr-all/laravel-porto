<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Containers\AppSection\CustomField\Enums\CustomFieldTypeEnum;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateCustomFieldRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        $type = $this->input('fieldable_type');
        if (is_string($type) && $type !== '') {
            $this->merge([
                'fieldable_type' => ContainerAliasEnum::toCanonicalMorphAlias($type),
            ]);
        }

        if ($this->exists('fieldable_id')) {
            $this->merge([
                'fieldable_id' => (string) $this->input('fieldable_id'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'fieldable_id' => 'required|string|max:36',
            'fieldable_type' => ['required', Rule::in(ContainerAliasEnum::customFieldableTypes())],
            'type' => ['required', Rule::enum(CustomFieldTypeEnum::class)],
            'payload' => 'required|array',
            'position' => 'sometimes|integer|min:0',
        ];
    }
}
