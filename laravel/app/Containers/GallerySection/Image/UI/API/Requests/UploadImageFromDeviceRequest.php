<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UploadImageFromDeviceRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,svg,webp,bmp|max:15360',
        ];
    }
}
