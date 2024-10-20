<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\Repositories;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;

class AlbumRepository
{
    /**
     * Get list of albums with cursor pagination
     *
     * @return CursorPaginator
     */
    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Album::class)
                           ->with(['tags'])
                           ->orderByDesc('created_at')
                           ->cursorPaginate(100);
    }

    /**
     * Get list of core albums (without versions) for Artist
     *
     * @param Artist $artist
     * @return Collection
     */
    public function listAlbumsWithoutVersions(Artist $artist): Collection
    {
        return QueryBuilder::for($artist->albums())
                           ->whereNull('parent_id')
                           ->get();
    }

    /**
     * Get list of albums by name for Artist
     *
     * @param Artist $artist
     * @param string $name
     * @return Album
     */
    public function listAlbumsByName(Artist $artist, string $name): Album
    {
        return QueryBuilder::for($artist->albums())
                           ->where('name', 'like', '%' . $name . '%')
                           ->first();
    }

    public function updateOrCreate(CreateAlbumDto $dto)
    {
        return Album::updateOrCreate([
            'name' => $dto->name,
            'date' => $dto->date,
        ], [
            'parent_id' => $dto->parent_id,
            'album_type_id' => $dto->album_type_id,
            'attributes' => $dto->attributes,
            'description' => $dto->description,
            'is_date_verified' => $dto->is_date_verified,
            'image' => $dto->image,
            'path' => $dto->path
        ]);
    }
}
