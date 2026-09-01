<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class SaveVideoRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [];
    }
}
