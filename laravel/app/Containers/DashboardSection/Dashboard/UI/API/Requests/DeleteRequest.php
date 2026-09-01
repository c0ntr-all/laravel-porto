<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Dashboard\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class DeleteRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
