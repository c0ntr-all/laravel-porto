<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use App\Ship\Rules\ValidateWindowsFilePath;

class UploadImagesFromWindowsRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $rootFolder = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));
        $disk = (string) config('image.windows.disk', 'windows_f');

        return [
            'paths' => 'required|array|min:1',
            'paths.*' => [
                'required',
                'string',
                new ValidateWindowsFilePath($rootFolder, $disk, ImageMimeEnum::toArray()),
            ],
        ];
    }
}
