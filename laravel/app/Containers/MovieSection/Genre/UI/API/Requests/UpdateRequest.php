<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Genre\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $genre = $this->route('genre');

        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('movie_genres', 'name')->ignore($genre),
            ],
            'slug' => [
                'sometimes',
                'nullable',
                'string',
                'max:100',
                Rule::unique('movie_genres', 'slug')->ignore($genre),
            ],
            'kp_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::unique('movie_genres', 'kp_id')->ignore($genre),
            ],
        ];
    }
}
