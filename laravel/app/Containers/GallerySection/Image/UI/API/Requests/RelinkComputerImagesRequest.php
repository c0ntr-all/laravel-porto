<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use InvalidArgumentException;

class RelinkComputerImagesRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'path' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (!is_string($value)) {
                        $fail('The path must be a Windows folder.');
                        return;
                    }

                    try {
                        $disk = (string) config('image.windows.disk', 'windows_f');
                        $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));
                        WindowsPathHelper::assertReadableDirectory($value, $disk, $root);
                    } catch (InvalidArgumentException $exception) {
                        $fail($exception->getMessage());
                    }
                },
            ],
        ];
    }

    public function windowsPath(): string
    {
        return WindowsPathHelper::normalizeWindows((string) $this->validated('path'));
    }
}
