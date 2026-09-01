<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UploadImageFromWebRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'link' => 'required|url|max:8192',
        ];
    }
}
