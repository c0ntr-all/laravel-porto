<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Requests;

use App\Containers\TaskManagerSection\Reminder\Enums\ReminderIntervalEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ReminderCreateRequest extends FormRequest
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
            'interval' => ['sometimes', Rule::in(ReminderIntervalEnum::toArray())],
            'to_remind_before' => 'sometimes|string|max:50',
            'is_active' => 'required|boolean',
            'datetime' => 'required|date_format:Y-m-d H:i'
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if ($this->task->reminder()->exists()) {
                $validator->errors()->add('reminder', 'Task can have only one reminder.');
            }
        });
    }

    public function messages(): array
    {
        return array_merge([
            'datetime.required' => 'The datetime must be specified',
        ], parent::messages());
    }
}
