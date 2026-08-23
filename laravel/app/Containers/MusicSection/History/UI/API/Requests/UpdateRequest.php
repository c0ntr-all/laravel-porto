<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        return parent::authorize()
            && $this->route('history')?->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'track_id' => 'sometimes|integer|exists:music_tracks,id',
        ];
    }
}
