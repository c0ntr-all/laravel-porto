<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateCustomFieldRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'payload' => 'sometimes|required|array',
            'position' => 'sometimes|integer|min:0',
        ];
    }
}
