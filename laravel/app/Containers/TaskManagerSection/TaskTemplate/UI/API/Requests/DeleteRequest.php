<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskTemplate\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class DeleteRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
