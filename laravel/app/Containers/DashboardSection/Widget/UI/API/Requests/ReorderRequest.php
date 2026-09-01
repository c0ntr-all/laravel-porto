<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ReorderRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|distinct|exists:App\Containers\DashboardSection\Widget\Models\Widget,id',
        ];
    }
}
