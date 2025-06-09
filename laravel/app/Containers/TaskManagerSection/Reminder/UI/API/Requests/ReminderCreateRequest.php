<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            'interval' => 'required|string|max:50',
            'to_remind_before' => 'sometimes|string|max:50',
            'is_active' => 'sometimes|boolean',
            'datetime' => 'required|date_format:Y-m-d H:i'
        ];
    }

    public function messages(): array
    {
        return array_merge([
            'datetime.required' => 'The datetime must be specified',
        ], parent::messages());
    }
}
