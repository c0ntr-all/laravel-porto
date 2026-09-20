<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Movie\UI\API\Requests;

use App\Containers\MovieSection\Movie\Enums\MovieTypeEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $movie = $this->route('movie');

        return [
            'kp_id' => [
                'sometimes',
                'integer',
                'min:1',
                Rule::unique('movies', 'kp_id')->ignore($movie),
            ],
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|nullable|string|max:10000',
            'short_description' => 'sometimes|nullable|string|max:2000',
            'year' => 'sometimes|integer|min:1888|max:2100',
            'type' => ['sometimes', Rule::enum(MovieTypeEnum::class)],
            'cover' => 'sometimes|nullable|url|max:2048',
            'kp_rating' => 'sometimes|nullable|numeric|between:0,10',
            'kp_img' => 'sometimes|nullable|url|max:2048',
            'genre_ids' => 'sometimes|array',
            'genre_ids.*' => 'integer|distinct|exists:movie_genres,id',
            'country_ids' => 'sometimes|array',
            'country_ids.*' => 'integer|distinct|exists:countries,id',
        ];
    }
}
