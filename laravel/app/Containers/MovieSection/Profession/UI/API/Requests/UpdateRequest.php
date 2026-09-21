<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Profession\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $profession = $this->route('profession');

        return [
            'en_name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('movie_professions', 'en_name')->ignore($profession),
            ],
            'name' => 'sometimes|nullable|string|max:150',
        ];
    }
}
