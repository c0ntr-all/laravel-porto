<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Requests;

use App\Containers\TaskManagerSection\Reminder\Enums\ReminderOccurrenceStatusEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class ReminderOccurrenceCreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            // Client may only create completion records; notifications are system-generated.
            'status' => [
                'sometimes',
                'string',
                Rule::in([ReminderOccurrenceStatusEnum::COMPLETED->value]),
            ],
            'completed_at' => ['sometimes', 'date_format:Y-m-d H:i:s'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in' => 'Only completed occurrences can be created via API.',
        ];
    }
}
