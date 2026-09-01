<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UploadUserAvatarRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'file' => 'required|image|mimes:jpeg,png,jpg,gif,webp,bmp|max:5120',
        ];
    }
}
