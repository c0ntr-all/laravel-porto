<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Requests;

use App\Containers\TaskManagerSection\Reminder\Enums\ReminderTimeUnitEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class ReminderUpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'datetime' => ['sometimes', 'date_format:Y-m-d H:i'],
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

    public function validated($key = null, $default = null): mixed
    {
        $validated = parent::validated();
        $mapped = [];

        if (array_key_exists('datetime', $validated)) {
            $mapped['datetime'] = $validated['datetime'];
        }

        if (array_key_exists('is_active', $validated)) {
            $mapped['is_active'] = $validated['is_active'];
        }

        if (array_key_exists('interval', $validated)) {
            $mapped['interval_value'] = data_get($validated, 'interval.value');
            $mapped['interval_unit'] = data_get($validated, 'interval.unit');
        }

        if (array_key_exists('to_remind_before', $validated)) {
            $mapped['to_remind_before_value'] = data_get($validated, 'to_remind_before.value');
            $mapped['to_remind_before_unit'] = data_get($validated, 'to_remind_before.unit');
        }

        if ($key !== null) {
            return data_get($mapped, $key, $default);
        }

        return $mapped;
    }
}
