<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Season\UI\API\Requests;

use App\Containers\MovieSection\Season\Models\Season;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $season = $this->route('season');
        $movieId = $this->input('movie_id', $season instanceof Season ? $season->movie_id : null);

        return [
            'movie_id' => 'sometimes|integer|exists:movies,id',
            'kp_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::unique('movie_seasons', 'kp_id')->ignore($season),
            ],
            'kp_season_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::unique('movie_seasons', 'kp_season_id')->ignore($season),
            ],
            'kp_movie_id' => 'sometimes|nullable|integer|min:1',
            'name' => 'sometimes|nullable|string|max:255',
            'en_name' => 'sometimes|nullable|string|max:255',
            'number' => [
                'sometimes',
                'integer',
                'min:0',
                Rule::unique('movie_seasons', 'number')
                    ->where('movie_id', $movieId)
                    ->ignore($season),
            ],
            'air_date' => 'sometimes|nullable|date_format:Y-m-d',
            'episodes_count' => 'sometimes|nullable|integer|min:0',
            'duration' => 'sometimes|nullable|integer|min:0',
            'poster' => 'sometimes|nullable|url|max:2048',
            'poster_preview' => 'sometimes|nullable|url|max:2048',
        ];
    }
}
