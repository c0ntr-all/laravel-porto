<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Playlist\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class CreateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'required|max:50'
        ];
    }
}
