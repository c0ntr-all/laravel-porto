<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Data\Repositories;

use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Tag\Data\Filters\ArtistTagsFilter;
use App\Containers\MusicSection\Track\Data\Filters\TrackRateFilter;
use App\Containers\MusicSection\Track\Data\Filters\TrackSearchFilter;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Data\DTO\UpdateTrackDto;
use App\Containers\MusicSection\Track\Data\Sorts\TrackRateSort;
use App\Containers\MusicSection\Track\Enums\TrackArtistRoleEnum;
use App\Containers\MusicSection\Track\Models\Track;
use App\Ship\Parents\QueryBuilder\QueryBuilder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\CursorPaginator;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedSort;

class TrackRepository
{
    public const DEFAULT_PER_PAGE = 24;

    public function getWithCursor(): CursorPaginator
    {
        return QueryBuilder::for(Track::class)
                           ->allowedFilters($this->allowedFilters())
                           ->allowedSorts($this->allowedSorts())
                           ->allowedIncludes(['tags', 'artists', 'album'])
                           ->with(['tags', 'artists', 'rate', 'album.albumType'])
                           ->defaultSort('-created_at')
                           ->orderByDesc('music_tracks.id')
                           ->cursorPaginate(self::DEFAULT_PER_PAGE);
    }

    /**
     * Tracks linked to the artist (primary and featured), not every track of their albums.
     */
    public function listTracksByArtistWithCursor(Artist $artist): CursorPaginator
    {
        return QueryBuilder::for($artist->tracks())
                           ->allowedFilters($this->allowedFilters(withPivotRole: true))
                           ->allowedSorts($this->allowedSorts())
                           ->with(['tags', 'artists', 'rate', 'album.albumType'])
                           ->defaultSort('-created_at')
                           ->orderByDesc('music_tracks.id')
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
            'credits' => $dto->credits,
            'cd' => $dto->cd,
            'disc_id' => $dto->disc_id,
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
            'disc_id' => $dto->disc_id,
            'credits' => $dto->credits,
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
    private function allowedFilters(bool $withPivotRole = false): array
    {
        $filters = [
            AllowedFilter::partial('name'),
            AllowedFilter::exact('album_id'),
            AllowedFilter::exact('cd'),
            AllowedFilter::exact('disc_id'),
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
            AllowedFilter::custom('tags', new ArtistTagsFilter()),
            AllowedFilter::callback('tags_match', function (Builder $query): void {
                unset($query);
            }),
            AllowedFilter::callback('tags_nested', function (Builder $query): void {
                unset($query);
            }),
            AllowedFilter::custom('rate', new TrackRateFilter()),
        ];

        if ($withPivotRole) {
            $filters[] = AllowedFilter::callback('role', function (Builder $query, mixed $value): void {
                $raw = is_array($value) ? (string) ($value[0] ?? '') : (string) $value;
                $role = TrackArtistRoleEnum::tryFrom($raw);
                if ($role === null) {
                    return;
                }

                $query->where('music_track_artist.role', $role->value);
            });
        }

        return $filters;
    }

    /**
     * @return list<string|AllowedSort>
     */
    private function allowedSorts(): array
    {
        return [
            AllowedSort::field('name', 'music_tracks.name'),
            AllowedSort::field('created_at', 'music_tracks.created_at'),
            AllowedSort::field('number', 'music_tracks.number'),
            AllowedSort::custom('rate', new TrackRateSort()),
        ];
    }
}
