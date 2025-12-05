<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\API\Requests;

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
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
            'date' => 'required|date_format:Y-m-d',
            'time' => 'sometimes|date_format:H:i|nullable',
            'tags' => 'sometimes|array',
            'tags.*' => 'sometimes|string',
            'new_tags' => 'sometimes|array',
            'new_tags.*' => 'sometimes|string|max:20'
        ];
    }
}
