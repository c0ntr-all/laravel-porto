<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateImageRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'description' => 'sometimes|nullable|string|max:5000',
        ];
    }
}
