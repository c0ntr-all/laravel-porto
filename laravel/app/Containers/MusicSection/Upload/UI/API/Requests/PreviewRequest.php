<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Requests;

use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Ship\Parents\Requests\AdminRequest;
use InvalidArgumentException;

class PreviewRequest extends AdminRequest
{
    public function rules(): array
    {
        return [
            'path' => [
                'required',
                'string',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    try {
                        $linuxPath = PathHelper::toLinux((string) $value);
                    } catch (InvalidArgumentException $exception) {
                        $fail($exception->getMessage());
                        return;
                    }

                    if (!is_dir($linuxPath)) {
                        $fail('The chosen catalog does not exist.');
                    }
                },
            ],
        ];
    }
}
