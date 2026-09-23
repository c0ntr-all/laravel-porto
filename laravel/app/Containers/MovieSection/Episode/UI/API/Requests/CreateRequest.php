<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Episode\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'season_id' => 'required|integer|exists:movie_seasons,id',
            'kp_id' => 'sometimes|nullable|integer|min:1|unique:movie_episodes,kp_id',
            'kp_season_id' => 'sometimes|nullable|integer|min:1',
            'name' => 'sometimes|nullable|string|max:255',
            'description' => 'sometimes|nullable|string|max:10000',
            'en_description' => 'sometimes|nullable|string|max:10000',
            'number' => [
                'required',
                'integer',
                'min:0',
                Rule::unique('movie_episodes', 'number')->where('season_id', $this->input('season_id')),
            ],
            'duration' => 'sometimes|nullable|integer|min:0',
            'air_date' => 'sometimes|nullable|date_format:Y-m-d',
            'still' => 'sometimes|nullable|url|max:2048',
            'still_preview' => 'sometimes|nullable|url|max:2048',
        ];
    }
}
