<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ListFranchiseMoviesRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'per_page' => 'sometimes|integer|min:1|max:100',
        ];
    }
}
