<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'name' => 'sometimes|string',
            'description' => 'sometimes|string|nullable',
            'tags' => 'sometimes|array',
            'tags.*' => 'sometimes|string',
            'image_file' => 'sometimes|image|mimes:jpeg,png,jpg,gif,svg|max:8192|nullable',
        ];
    }
}
