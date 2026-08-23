<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\Repositories;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Data\DTO\UpdateAlbumDto;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

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
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('album_type_id'),
                               AllowedFilter::exact('parent_id'),
                           ])
                           ->allowedSorts(['name', 'created_at', 'date'])
                           ->allowedIncludes(['tags', 'artists'])
                           ->with(['tags', 'artists'])
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
                           ->allowedFilters([
                               AllowedFilter::partial('name'),
                               AllowedFilter::exact('album_type_id'),
                           ])
                           ->allowedSorts(['name', 'date', 'created_at'])
                           ->whereNull('parent_id')
                           ->get();
    }

    /**
     * Get list of albums by name for Artist
     *
     * @param Artist $artist
     * @param string $name
     * @return Album|null
     */
    public function listAlbumsByName(Artist $artist, string $name): ?Album
    {
        return QueryBuilder::for($artist->albums())
                           ->where('name', 'like', '%' . $name . '%')
                           ->first();
    }

    public function findByPath(string $path): ?Album
    {
        return Album::query()->where('path', $path)->first();
    }

    public function create(CreateAlbumDto $dto): Album
    {
        return Album::create([
            'parent_id' => $dto->parent_id,
            'album_type_id' => $dto->album_type_id,
            'name' => $dto->name,
            'description' => $dto->description,
            'attributes' => $dto->attributes,
            'date' => $dto->date,
            'is_date_verified' => $dto->is_date_verified,
            'image' => $dto->image,
            'path' => $dto->path,
        ]);
    }

    public function update(Album $album, UpdateAlbumDto $dto): Album
    {
        $album->update(collect($dto->toArray())->filter(fn (mixed $value) => $value !== null)->all());

        return $album;
    }

    public function delete(Album $album): ?bool
    {
        return $album->delete();
    }

    public function syncArtists(Album $album, array $artistIds): array
    {
        return $album->artists()->sync($artistIds);
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
