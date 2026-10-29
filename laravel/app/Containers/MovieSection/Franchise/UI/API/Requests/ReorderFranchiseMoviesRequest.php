<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class ReorderFranchiseMoviesRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'movie_ids' => 'required|array|min:1',
            'movie_ids.*' => 'integer|distinct|exists:movies,id',
        ];
    }
}
