<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ConvertChecklistItemToTaskRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
