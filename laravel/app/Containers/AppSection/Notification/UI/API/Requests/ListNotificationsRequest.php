<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListNotificationsRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'unread_only' => 'sometimes|boolean',
            'per_page' => 'sometimes|integer|min:1|max:100',
            'cursor' => 'sometimes|string',
            'page' => 'sometimes|integer|min:1',
        ];
    }
}
