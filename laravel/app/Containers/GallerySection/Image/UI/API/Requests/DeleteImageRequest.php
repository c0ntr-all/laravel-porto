<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;

class DeleteImageRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'album_id' => 'sometimes|integer|exists:gallery_albums,id',
        ];
    }

    public function albumId(): ?int
    {
        $albumId = $this->validated('album_id');

        return $albumId === null ? null : (int) $albumId;
    }
}
