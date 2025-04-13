<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TaskProgressCreateRequest extends FormRequest
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
            'title' => 'required|string|max:100',
            'content' => 'sometimes|string|max:3000',
            'is_final' => 'sometimes|boolean',
            'finished_at' => 'required|date_format:Y-m-d H:i'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $task = $this->route('task');

            $isFinalProgressExists = $task->progress()->where('is_final', true)->exists();

            if ($isFinalProgressExists) {
                $validator->errors()->add('task_id', 'Нельзя добавить этап: задача уже завершена.');
            }
        });
    }

    public function messages()
    {
        return array_merge([
            'finished_at.required' => 'The datetime must be specified',
        ], parent::messages());
    }
}
