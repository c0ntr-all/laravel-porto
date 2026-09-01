<?php declare(strict_types=1);

namespace App\Containers\AppSection\Comment\UI\API\Requests;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        $type = $this->input('commentable_type');
        if (is_string($type) && $type !== '') {
            $this->merge([
                'commentable_type' => ContainerAliasEnum::toCanonicalMorphAlias($type),
            ]);
        }

        if ($this->exists('commentable_id')) {
            $this->merge([
                'commentable_id' => (string) $this->input('commentable_id'),
            ]);
        }
    }

    public function rules(): array
    {
        return [
            'commentable_id' => 'required|string|max:36',
            'commentable_type' => ['required', Rule::in(ContainerAliasEnum::toArray())],
            'content' => 'required|string|max:1000',
        ];
    }
}
