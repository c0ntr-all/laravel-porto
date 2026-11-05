<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\API\Requests;

use App\Ship\Parents\Requests\AuthenticatedRequest;
use Illuminate\Validation\Rule;

class DeleteImageRequest extends AuthenticatedRequest
{
    public function rules(): array
    {
        return [
            'album_id' => [
                'sometimes',
                'integer',
                Rule::exists('gallery_albums', 'id')->where(function ($query): void {
                    $query->where('user_id', (int) $this->user()->id)
                        ->orWhereNull('user_id');
                }),
            ],
        ];
    }

    public function albumId(): ?int
    {
        $albumId = $this->validated('album_id');

        return $albumId === null ? null : (int) $albumId;
    }
}
