<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Requests;

use App\Containers\GallerySection\Video\Enums\VideoMimeEnum;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use App\Ship\Rules\ValidateWindowsFilePath;

class UploadVideosFromWindowsRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        $rootFolder = (string) config('video.windows.root_folder');
        $disk = (string) config('video.windows.disk', 'windows_f');

        return [
            'paths' => 'required|array|min:1',
            'paths.*' => [
                'required',
                'string',
                new ValidateWindowsFilePath($rootFolder, $disk, VideoMimeEnum::toArray()),
            ],
        ];
    }
}
