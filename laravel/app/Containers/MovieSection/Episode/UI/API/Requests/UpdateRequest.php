<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\API\Requests;

use App\Containers\MovieSection\Episode\Models\Episode;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class UpdateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $episode = $this->route('episode');
        $seasonId = $this->input('season_id', $episode instanceof Episode ? $episode->season_id : null);

        return [
            'season_id' => 'sometimes|integer|exists:movie_seasons,id',
            'kp_id' => [
                'sometimes',
                'nullable',
                'integer',
                'min:1',
                Rule::unique('movie_episodes', 'kp_id')->ignore($episode),
            ],
            'kp_season_id' => 'sometimes|nullable|integer|min:1',
            'name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:10000',
            'en_description' => 'sometimes|nullable|string|max:10000',
            'number' => [
                'sometimes',
                'integer',
                'min:0',
                Rule::unique('movie_episodes', 'number')
                    ->where('season_id', $seasonId)
                    ->ignore($episode),
            ],
            'duration' => 'sometimes|nullable|integer|min:0',
            'air_date' => 'sometimes|nullable|date_format:Y-m-d',
            'still' => 'sometimes|nullable|url|max:2048',
            'still_preview' => 'sometimes|nullable|url|max:2048',
        ];
    }
}
