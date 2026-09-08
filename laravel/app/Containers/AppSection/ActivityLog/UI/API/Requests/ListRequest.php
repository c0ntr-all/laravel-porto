<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'filter.loggable_id' => 'sometimes|string',
            'filter.loggable_type' => 'sometimes|string',
            'filter.correlation_uuid' => 'sometimes|uuid',
            'filter.event_type' => 'sometimes|string',
            'include' => 'sometimes|string',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'cursor' => 'sometimes|string',
        ];
    }
}
