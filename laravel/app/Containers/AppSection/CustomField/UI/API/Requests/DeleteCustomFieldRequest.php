<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class DeleteCustomFieldRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
