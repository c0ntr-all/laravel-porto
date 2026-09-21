<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class AttachMovieRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'movie_id' => 'required|integer|exists:movies,id',
        ];
    }
}
