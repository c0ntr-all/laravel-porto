<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'kp_id' => 'required|integer|min:1|unique:movie_persons,kp_id',
            'profession_id' => 'required|integer|exists:movie_professions,id',
            'name' => 'required|string|max:255',
            'en_name' => 'sometimes|nullable|string|max:255',
            'photo' => 'sometimes|nullable|url|max:2048',
        ];
    }
}
