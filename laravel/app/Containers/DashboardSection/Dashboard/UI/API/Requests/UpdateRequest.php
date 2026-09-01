<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:80',
            'description' => 'sometimes|nullable|string|max:255',
            'is_default' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer|min:0',
        ];
    }
}
