<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'en_name' => 'required|string|max:100|unique:movie_professions,en_name',
            'name' => 'sometimes|nullable|string|max:150',
        ];
    }
}
