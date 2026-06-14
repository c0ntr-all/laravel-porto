<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class GetAlbumRequest extends AuthenticatedRequest
{

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return [
            //
        ];
    }
}
