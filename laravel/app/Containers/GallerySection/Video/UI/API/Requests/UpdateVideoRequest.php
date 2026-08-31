<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UpdateVideoRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'description' => 'sometimes|nullable|string|max:5000',
        ];
    }
}
