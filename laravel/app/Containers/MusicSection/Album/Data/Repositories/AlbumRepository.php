<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\Repositories;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Data\DTO\UpdateAlbumDto;
use App\Containers\MusicSection\Album\Data\Filters\AlbumNameFilter;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;
use Spatie\LaravelData\Optional;
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
        $query = QueryBuilder::for(Album::class, request())
                           ->allowedFilters($this->allowedFilters())
                           ->allowedSorts(['name', 'created_at', 'date'])
                           ->allowedIncludes(['tags', 'artists', 'versions', 'parent'])
                           ->with(['tags', 'artists']);

        if (!request()->has('filter.parent_id')) {
            $query->whereNull('parent_id')->with('versions');
        }

        return $query
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->cursorPaginate(24);
    }

    /**
     * Root albums for an artist, with nested versions.
     */
    public function listAlbumsWithoutVersions(Artist $artist): Collection
    {
        return QueryBuilder::for($artist->albums(), request())
                           ->allowedFilters([
                               AllowedFilter::custom('name', new AlbumNameFilter()),
                               AllowedFilter::exact('album_type_id'),
                           ])
                           ->allowedSorts(['name', 'date', 'created_at'])
                           ->with(['versions', 'artists', 'tags'])
                           ->whereNull('parent_id')
                           ->get();
    }

    public function findRootByName(Artist $artist, string $name, ?int $albumTypeId = null): ?Album
    {
        $query = $artist->albums()
            ->whereNull('parent_id')
            ->where(function (Builder $query): void {
                $query->whereNull('edition')->orWhere('edition', '');
            })
            ->where('name', $name);

        if ($albumTypeId !== null) {
            $query->where('album_type_id', $albumTypeId);
        }

        return $query->first();
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
            'edition' => $dto->edition,
            'date' => $dto->date,
            'is_date_verified' => $dto->is_date_verified,
            'image' => $dto->image,
            'path' => $dto->path,
        ]);
    }

    public function update(Album $album, UpdateAlbumDto $dto): Album
    {
        $attributes = [];

        foreach ($dto->toArray() as $key => $value) {
            if ($value instanceof Optional) {
                continue;
            }

            if ($value === null && !in_array($key, ['parent_id', 'edition'], true)) {
                continue;
            }

            $attributes[$key] = $value;
        }

        if ($attributes !== []) {
            $album->update($attributes);
        }

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
            'edition' => $dto->edition,
            'description' => $dto->description,
            'is_date_verified' => $dto->is_date_verified,
            'image' => $dto->image,
            'path' => $dto->path
        ]);
    }

    /**
     * @return list<AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::custom('name', new AlbumNameFilter()),
            AllowedFilter::callback('artist', function (Builder $query, mixed $value): void {
                $term = $this->like((string) $value);
                if ($term === null) {
                    return;
                }

                $query->whereHas('artists', function (Builder $artists) use ($term): void {
                    $artists->where('music_artists.name', 'like', $term);
                });
            }),
            AllowedFilter::exact('album_type_id'),
            AllowedFilter::exact('parent_id'),
        ];
    }

    private function like(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        return '%'.addcslashes($value, '%_\\').'%';
    }
}
