<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        return parent::authorize();
    }

    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                'max:50',
                Rule::unique('tags', 'name')->where('user_id', auth()->id()),
            ],
            'description' => 'sometimes|string|max:30000',
            'icon' => 'sometimes|string|max:50',
            'color' => ['sometimes', 'string', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'parent_id' => [
                'sometimes',
                'nullable',
                Rule::exists('tags', 'id')->where('user_id', auth()->id()),
            ],
        ];
    }
}
