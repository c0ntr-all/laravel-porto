<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Task\UI\API\Requests;

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
            'task_list_id' => 'sometimes|exists:App\Containers\TaskManagerSection\TaskList\Models\TaskList,id',
            'title' => 'sometimes|string|max:70',
            'content' => 'sometimes|max:3000',
        ];
    }
}
