<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|string|unique:music_tags|max:50',
            'content' => 'sometimes|string|max:30000',
            'parent_id' => 'sometimes|int',
            'is_base' => 'sometimes|boolean',
        ];
    }
}
