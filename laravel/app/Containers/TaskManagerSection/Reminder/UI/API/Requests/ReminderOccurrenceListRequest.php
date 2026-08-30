<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Reminder\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ReminderOccurrenceListRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
