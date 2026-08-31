<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateAlbumRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:255',
            'description' => 'sometimes|nullable|string|max:5000',
            'image' => 'sometimes|nullable|string|max:255',
        ];
    }
}
