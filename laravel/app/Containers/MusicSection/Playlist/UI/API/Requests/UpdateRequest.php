<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateRequest extends AuthenticatedRequest
{
    public function authorize(): bool
    {
        return parent::authorize()
            && $this->route('playlist')?->user_id === $this->user()->id;
    }

    public function rules(): array
    {
        return [
            'name' => 'sometimes|string|max:50',
            'description' => 'sometimes|string|nullable|max:30000',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }
}
