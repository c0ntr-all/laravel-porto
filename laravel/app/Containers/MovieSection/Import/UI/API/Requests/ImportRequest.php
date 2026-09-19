<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ImportRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'kp_id' => 'required|integer|min:1',
        ];
    }
}
