<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Requests;

use App\Containers\TaskManagerSection\Reminder\Enums\ReminderTimeUnitEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class ReminderCreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'datetime' => ['required', 'date_format:Y-m-d H:i'],
            'is_active' => ['sometimes', 'boolean'],
            'interval' => ['sometimes', 'nullable', 'array'],
            'interval.value' => ['required_with:interval.unit', 'integer', 'min:1', 'max:999'],
            'interval.unit' => [
                'required_with:interval.value',
                'string',
                Rule::in(ReminderTimeUnitEnum::intervalUnitValues()),
            ],
            'to_remind_before' => ['sometimes', 'nullable', 'array'],
            'to_remind_before.value' => ['required_with:to_remind_before.unit', 'integer', 'min:1', 'max:9999'],
            'to_remind_before.unit' => [
                'required_with:to_remind_before.value',
                'string',
                Rule::in(ReminderTimeUnitEnum::remindBeforeUnitValues()),
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator): void {
            if ($this->route('task')?->reminder()->exists()) {
                $validator->errors()->add('reminder', 'Task can have only one reminder.');
            }
        });
    }

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();

        $mapped = [
            'datetime' => $validated['datetime'],
            'is_active' => $validated['is_active'] ?? true,
            'interval_value' => data_get($validated, 'interval.value'),
            'interval_unit' => data_get($validated, 'interval.unit'),
            'to_remind_before_value' => data_get($validated, 'to_remind_before.value'),
            'to_remind_before_unit' => data_get($validated, 'to_remind_before.unit'),
        ];

        if ($key !== null) {
            return data_get($mapped, $key, $default);
        }

        return $mapped;
    }

    public function messages(): array
    {
        return [
            'datetime.required' => 'The datetime must be specified',
            'datetime.date_format' => 'The datetime must match format Y-m-d H:i',
        ];
    }
}
