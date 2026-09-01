<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Data\Repositories;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Image\Data\DTO\CreateImageDto;
use App\Containers\GallerySection\Image\Data\DTO\UpdateImageDto;
use App\Containers\GallerySection\Image\Models\Image;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class ImageRepository
{
    public function listByAlbum(Album $album): Collection
    {
        return $album->images()->orderByDesc('created_at')->get();
    }

    public function findSavedCopy(int $userId, int|string $albumId, string $originId): ?Image
    {
        return Image::query()
            ->where('user_id', $userId)
            ->where('album_id', $albumId)
            ->where(function ($query) use ($originId) {
                $query->where('id', $originId)->orWhere('saved_from_id', $originId);
            })
            ->first();
    }

    public function findForUser(string $id, int $userId): ?Image
    {
        return Image::query()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->first();
    }

    public function create(Album $album, CreateImageDto $dto): Image
    {
        $image = $album->images()->make($dto->toArray());

        if ($dto->id !== null && $dto->id !== '') {
            $image->id = $dto->id;
        }

        $image->save();

        return $image;
    }

    public function update(Image $image, UpdateImageDto $dto): Image
    {
        $attributes = [];

        if (!($dto->description instanceof Optional)) {
            $attributes['description'] = $dto->description;
        }

        if ($attributes !== []) {
            $image->update($attributes);
        }

        return $image->refresh();
    }

    public function delete(Image $image): bool
    {
        return (bool) $image->delete();
    }
}
