<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:100|unique:movie_genres,name',
            'slug' => 'sometimes|nullable|string|max:100|unique:movie_genres,slug',
            'kp_id' => 'sometimes|nullable|integer|min:1|unique:movie_genres,kp_id',
        ];
    }
}
