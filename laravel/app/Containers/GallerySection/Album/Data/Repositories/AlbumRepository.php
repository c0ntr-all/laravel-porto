<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\Data\Repositories;

use App\Containers\GallerySection\Album\Data\DTO\AlbumCreateData;
use App\Containers\GallerySection\Album\Data\DTO\AlbumUpdateData;
use App\Containers\GallerySection\Album\Models\Album;
use Illuminate\Database\Eloquent\Collection;
use Spatie\LaravelData\Optional;

class AlbumRepository
{
    /**
     * @param list<string> $with
     */
    public function list(array $with = []): Collection
    {
        $query = Album::query()->orderByDesc('id');

        if ($with !== []) {
            $query->with($with);
        }

        return $query->withCount(['images', 'videos'])->get();
    }

    /**
     * @param list<string> $with
     */
    public function getById(Album $album, array $with = []): Album
    {
        if ($with !== []) {
            $album->load($with);
        }

        return $album->loadCount(['images', 'videos']);
    }

    public function create(AlbumCreateData $dto): Album
    {
        return Album::create($dto->toArray());
    }

    public function update(Album $album, AlbumUpdateData $dto): Album
    {
        $attributes = [];

        foreach (['name', 'description', 'image'] as $field) {
            if (!($dto->{$field} instanceof Optional)) {
                $attributes[$field] = $dto->{$field};
            }
        }

        if ($attributes !== []) {
            $album->update($attributes);
        }

        return $album->refresh();
    }

    public function delete(Album $album): bool
    {
        return (bool) $album->delete();
    }

    public function getSystemAlbumByCode(string $systemCode): Album
    {
        return Album::withoutGlobalScopes()->where('system_code', $systemCode)->firstOrFail();
    }
}
