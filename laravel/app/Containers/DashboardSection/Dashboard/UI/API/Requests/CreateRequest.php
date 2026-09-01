<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:80',
            'description' => 'sometimes|nullable|string|max:255',
            'is_default' => 'sometimes|boolean',
            'with_defaults' => 'sometimes|boolean',
        ];
    }
}
