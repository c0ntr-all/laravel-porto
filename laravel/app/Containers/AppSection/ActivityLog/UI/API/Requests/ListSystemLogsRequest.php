<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListSystemLogsRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'correlation_uuid' => 'required|uuid',
        ];
    }
}
