<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListRequest extends AuthenticatedRequest
{

    public function rules(): array
    {
        return [
            'filter.loggable_id' => 'required|string',
            'filter.loggable_type' => 'required|string',
        ];
    }
}
