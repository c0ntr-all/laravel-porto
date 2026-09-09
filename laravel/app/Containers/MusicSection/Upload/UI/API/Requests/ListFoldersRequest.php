<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Requests;

use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Ship\Parents\Requests\AdminRequest;
use InvalidArgumentException;

class ListFoldersRequest extends AdminRequest
{
    protected function prepareForValidation(): void
    {
        if (!$this->filled('path')) {
            $this->merge([
                'path' => PathHelper::libraryRootWindows(),
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
                    try {
                        PathHelper::resolveLibraryDirectory((string) $value);
                    } catch (InvalidArgumentException $exception) {
                        $fail($exception->getMessage());
                    }
                },
            ],
        ];
    }

    public function windowsPath(): string
    {
        return PathHelper::normalizeWindows((string) $this->validated('path'));
    }
}
