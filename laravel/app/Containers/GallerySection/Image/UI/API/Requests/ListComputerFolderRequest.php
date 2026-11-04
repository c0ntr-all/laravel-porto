<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Requests\AuthenticatedRequest;
use InvalidArgumentException;

class ListComputerFolderRequest extends AuthenticatedRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('path')) {
            $this->merge([
                'path' => (string) config('image.windows.root_folder', config('app.windows_images_root_folder')),
            ]);
        }
    }

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
            'page' => 'sometimes|integer|min:1',
            'per_page' => 'sometimes|integer|min:1|max:200',
        ];
    }

    public function windowsPath(): string
    {
        return WindowsPathHelper::normalizeWindows((string) $this->validated('path'));
    }

    public function page(): int
    {
        return max(1, (int) $this->validated('page', 1));
    }

    public function perPage(): int
    {
        $perPage = (int) $this->validated('per_page', 60);

        return max(1, min(200, $perPage));
    }
}
