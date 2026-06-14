<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\UI\API\Requests;

use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Ship\Parents\Requests\AdminRequest;

class UploadRequest extends AdminRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'path' => [
                'required',
                'string',
                function ($attribute, $value, $fail) {
                    $linuxPath = PathHelper::windowsToLinux($value);
                    if (!PathHelper::exists($linuxPath)) {
                        $fail('The chosen catalog doesn\'t exists!');
                    }
                }
            ],
            'is_preview' => 'sometimes|boolean'
        ];
    }
}
