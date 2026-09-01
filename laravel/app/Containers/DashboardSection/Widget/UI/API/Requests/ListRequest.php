<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
