<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Person\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $person = $this->route('person');

        return [
            'kp_id' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('movie_persons', 'kp_id')->ignore($person),
            ],
            'profession_id' => 'sometimes|nullable|integer|exists:movie_professions,id',
            'name' => 'sometimes|string|max:255',
            'en_name' => 'sometimes|nullable|string|max:255',
            'photo' => 'sometimes|nullable|url|max:2048',
        ];
    }
}
