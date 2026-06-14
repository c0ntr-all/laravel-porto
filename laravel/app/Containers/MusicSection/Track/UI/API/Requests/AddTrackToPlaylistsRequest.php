<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class AddTrackToPlaylistsRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'playlist_ids' => 'required|array',
            'playlist_ids.*' => 'required|numeric'
        ];
    }
}
