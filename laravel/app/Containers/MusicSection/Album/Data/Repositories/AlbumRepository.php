<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\Data\Repositories;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Data\DTO\UpdateAlbumDto;
use App\Containers\MusicSection\Album\Data\Filters\AlbumNameFilter;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Models\AlbumType;
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
                           ->allowedIncludes(['tags', 'artists', 'versions', 'parent', 'discs'])
                           ->with(['tags', 'artists', 'albumType', 'discs' => fn ($query) => $query->withCount('tracks')])
                           ->withCount('discs');

        if (!request()->has('filter.parent_id')) {
            $query->whereNull('parent_id')->with(['versions.albumType']);
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
                               $this->hasMultipleDiscsFilter(),
                           ])
                           ->allowedSorts(['name', 'date', 'created_at'])
                           ->with(['versions.albumType', 'albumType', 'artists', 'tags', 'discs' => fn ($query) => $query->withCount('tracks')])
                           ->withCount('discs')
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

    public function listTypes(): Collection
    {
        return AlbumType::query()->orderBy('id')->get();
    }

    public function findByPath(string $path): ?Album
    {
        return Album::query()->where('path', $path)->first();
    }

    /**
     * @param list<string> $paths
     * @return list<string>
     */
    public function existingPaths(array $paths): array
    {
        if ($paths === []) {
            return [];
        }

        return Album::query()
            ->whereIn('path', $paths)
            ->pluck('path')
            ->all();
    }

    public function findCanonical(Artist $artist, string $name, int $albumTypeId, ?string $edition = null): ?Album
    {
        $query = $artist->albums()
            ->where('name', $name)
            ->where('album_type_id', $albumTypeId);

        if ($edition === null || $edition === '') {
            $query->where(function (Builder $query): void {
                $query->whereNull('edition')->orWhere('edition', '');
            });
        } else {
            $query->where('edition', $edition);
        }

        return $query->first();
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
            AllowedFilter::callback('artist_id', function (Builder $query, mixed $value): void {
                $ids = $this->artistIds($value);
                if ($ids === []) {
                    return;
                }

                $query->whereHas('artists', function (Builder $artists) use ($ids): void {
                    $artists->whereIn('music_artists.id', $ids);
                });
            }),
            AllowedFilter::exact('album_type_id'),
            AllowedFilter::exact('parent_id'),
            $this->hasMultipleDiscsFilter(),
        ];
    }

    private function hasMultipleDiscsFilter(): AllowedFilter
    {
        return AllowedFilter::callback('has_multiple_discs', function (Builder $query, mixed $value): void {
            $enabled = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
            if ($enabled === true) {
                $query->has('discs', '>=', 2);

                return;
            }

            if ($enabled === false) {
                $query->has('discs', '<', 2);
            }
        });
    }

    /**
     * @return list<int>
     */
    private function artistIds(mixed $value): array
    {
        if (is_string($value) || is_numeric($value)) {
            $value = preg_split('/\s*,\s*/', (string) $value) ?: [];
        }

        if (!is_array($value)) {
            return [];
        }

        return collect($value)
            ->flatten()
            ->map(fn (mixed $id) => (int) $id)
            ->filter(fn (int $id) => $id > 0)
            ->unique()
            ->values()
            ->all();
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
