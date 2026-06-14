<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Containers\GallerySection\Image\Rules\ValidateImageExistence;
use App\Containers\GallerySection\Image\Rules\ValidateImageExtension;
use App\Ship\Parents\Requests\AuthenticatedRequest;

class UploadImagesFromWindowsRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        $windowsImagesRootFolder = config('app.windows_images_root_folder');

        return [
            'paths' => 'required|array',
            'paths.*' => [
                'required',
                'string',
                'starts_with:' . $windowsImagesRootFolder,
                new ValidateImageExtension(),
                new ValidateImageExistence()
            ]
        ];
    }
}
