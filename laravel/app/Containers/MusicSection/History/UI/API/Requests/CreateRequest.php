<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'track_id' => 'required|integer|exists:music_tracks,id',
        ];
    }
}
