<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Repositories;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Track\Data\Filters\TrackSearchFilter;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Data\DTO\UpdateTrackDto;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;

class TrackRepository
{
    public const DEFAULT_PER_PAGE = 24;

    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Track::class)
                           ->allowedFilters($this->allowedFilters())
                           ->allowedSorts(['name', 'created_at', 'number'])
                           ->allowedIncludes(['tags', 'artists', 'album'])
                           ->with(['tags', 'artists', 'rate', 'album.albumType'])
                           ->orderByDesc('created_at')
                           ->orderByDesc('id')
                           ->cursorPaginate(self::DEFAULT_PER_PAGE);
    }

    /**
     * Get list of all tracks for Artist
     *
     * @param array $albumIds
     * @return CursorPaginator
     */
    public function listTracksByAlbumIdsWithCursor(array $albumIds): CursorPaginator
    {
        return QueryBuilder::for(Track::whereIn('album_id', $albumIds))
                           ->allowedFilters($this->allowedFilters())
                           ->allowedSorts(['name', 'created_at', 'number'])
                           ->with(['tags', 'artists', 'rate'])
                           ->cursorPaginate(50);
    }

    public function addTrackToPlaylists(Track $track, array $playlists, array $pivotValues): array
    {
        return $track->playlists()->syncWithPivotValues($playlists, $pivotValues);
    }

    public function findByPath(string $path): ?Track
    {
        return Track::query()->where('path', $path)->first();
    }

    public function create(Album $album, CreateTrackDto $dto): Track
    {
        return $album->tracks()->create([
            'name' => $dto->name,
            'cd' => $dto->cd,
            'number' => $dto->number,
            'path' => $dto->path,
            'image' => $dto->image,
            'duration' => $dto->duration,
            'bitrate' => $dto->bitrate,
            'link' => $dto->link,
            'lyrics' => $dto->lyrics,
        ]);
    }

    /**
     * @param Album $album
     * @param CreateTrackDto $dto
     * @return Track
     */
    public function updateOrCreate(Album $album, CreateTrackDto $dto): Track
    {
        return $album->tracks()->updateOrCreate([
            'name' => $dto->name,
        ], [
            'cd' => $dto->cd,
            'number' => $dto->number,
            'path' => $dto->path,
            'image' => $dto->image,
            'duration' => $dto->duration,
            'bitrate' => $dto->bitrate,
            'link' => $dto->link,
            'lyrics' => $dto->lyrics,
        ]);
    }

    public function update(Track $track, UpdateTrackDto $dto): Track
    {
        $track->update(collect($dto->toArray())->filter(fn (mixed $value) => $value !== null)->all());

        return $track;
    }

    public function delete(Track $track): ?bool
    {
        return $track->delete();
    }

    public function syncArtists(Track $track, array $artistsIds): array
    {
        return $track->artists()->sync($artistsIds);
    }

    public function syncArtistsWithoutDetaching(Track $track, array $artistsIds): array
    {
        return $track->artists()->syncWithoutDetaching($artistsIds);
    }

    /**
     * @return list<AllowedFilter>
     */
    private function allowedFilters(): array
    {
        return [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('album_id'),
            AllowedFilter::custom('search', new TrackSearchFilter()),
            AllowedFilter::callback('artist', function (Builder $query, mixed $value): void {
                $term = trim((string) $value);
                if ($term === '') {
                    return;
                }

                $pattern = '%'.addcslashes($term, '%_\\').'%';
                $query->whereHas('artists', function (Builder $artists) use ($pattern): void {
                    $artists->where('music_artists.name', 'like', $pattern);
                });
            }),
            AllowedFilter::callback('album', function (Builder $query, mixed $value): void {
                $term = trim((string) $value);
                if ($term === '') {
                    return;
                }

                $pattern = '%'.addcslashes($term, '%_\\').'%';
                $query->whereHas('album', function (Builder $album) use ($pattern): void {
                    $album->where('music_albums.name', 'like', $pattern);
                });
            }),
        ];
    }
}
