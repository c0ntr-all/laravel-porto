<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskProgressUpdateRequest extends FormRequest
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
            'title' => 'sometimes|string|max:100',
            'content' => 'sometimes|string|max:3000',
            'is_final' => 'sometimes|bool',
        ];
    }
}
