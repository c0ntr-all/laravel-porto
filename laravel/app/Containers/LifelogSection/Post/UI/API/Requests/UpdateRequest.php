<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

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
            'attachments.*' => 'sometimes|uuid',
        ];
    }
}
