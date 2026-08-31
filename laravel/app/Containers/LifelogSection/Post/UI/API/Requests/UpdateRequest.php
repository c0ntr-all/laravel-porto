<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
            'date' => 'sometimes|date_format:Y-m-d',
            'time' => 'sometimes|date_format:H:i|nullable',
            'tags' => 'sometimes|array',
            'tags.*' => 'sometimes|string',
            'new_tags' => 'sometimes|array',
            'new_tags.*' => 'sometimes|string|unique:App\Containers\AppSection\Tag\Models\Tag,name',
            'deleted_attachment_ids' => 'sometimes|array',
            'deleted_attachments_ids.*' => 'sometimes|string',
            'attachments' => 'sometimes|array',
            'attachments.*.type' => [
                'required',
                Rule::in(ContainerAliasEnum::galleryFileableTypes()),
            ],
            'attachments.*.id' => 'required|uuid',
        ];
    }
}
