<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class UploadVideoFromDeviceRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            'file' => 'required|file|mimes:mp4,avi,mov,mkv,3gp|max:51200|nullable'
        ];
    }
}
