<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Album\Data\DTO\CreateAlbumDto;
use App\Containers\MusicSection\Album\Data\DTO\UpdateAlbumDto;
use App\Containers\MusicSection\Album\Models\Album;
use App\Containers\MusicSection\Album\Tasks\CreateAlbumTask;
use App\Containers\MusicSection\Album\Tasks\FindAlbumByPathTask;
use App\Containers\MusicSection\Album\Tasks\ListAlbumsByNameTask;
use App\Containers\MusicSection\Album\Tasks\SyncArtistsForAlbumTask;
use App\Containers\MusicSection\Album\Tasks\UpdateAlbumTask;
use App\Containers\MusicSection\Album\Tasks\UploadAlbumCoverTask;
use App\Containers\MusicSection\Artist\Data\DTO\CreateArtistDto;
use App\Containers\MusicSection\Artist\Data\DTO\UpdateArtistDto;
use App\Containers\MusicSection\Artist\Models\Artist;
use App\Containers\MusicSection\Artist\Tasks\CreateArtistTask;
use App\Containers\MusicSection\Artist\Tasks\FindArtistByNameTask;
use App\Containers\MusicSection\Artist\Tasks\FindArtistByPathTask;
use App\Containers\MusicSection\Artist\Tasks\SyncAlbumsForArtistTask;
use App\Containers\MusicSection\Artist\Tasks\UpdateArtistTask;
use App\Containers\MusicSection\Artist\Tasks\UploadArtistCoverTask;
use App\Containers\MusicSection\Tag\Data\DTO\SyncTagsDto;
use App\Containers\MusicSection\Tag\Tasks\ListTagsShortTask;
use App\Containers\MusicSection\Tag\Tasks\SyncTagsTask;
use App\Containers\MusicSection\Track\Data\DTO\CreateTrackDto;
use App\Containers\MusicSection\Track\Data\DTO\UpdateTrackDto;
use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Track\Tasks\CreateTrackTask;
use App\Containers\MusicSection\Track\Tasks\FindTrackByPathTask;
use App\Containers\MusicSection\Track\Tasks\SyncArtistsForTrackTask;
use App\Containers\MusicSection\Track\Tasks\UpdateTrackTask;
use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Data\Repositories\MusicUploadRepository;
use App\Containers\MusicSection\Upload\Enums\UploadTrackStatusEnum;
use App\Containers\MusicSection\Upload\Events\UploadProgressed;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\File;
use Illuminate\Support\Facades\DB;

class PersistLibraryTask extends ParentTask
{
    public function __construct(
        private readonly MusicUploadRepository $musicUploadRepository,
        private readonly FindArtistByPathTask $findArtistByPathTask,
        private readonly FindArtistByNameTask $findArtistByNameTask,
        private readonly CreateArtistTask $createArtistTask,
        private readonly UpdateArtistTask $updateArtistTask,
        private readonly UploadArtistCoverTask $uploadArtistCoverTask,
        private readonly FindAlbumByPathTask $findAlbumByPathTask,
        private readonly ListAlbumsByNameTask $listAlbumsByNameTask,
        private readonly CreateAlbumTask $createAlbumTask,
        private readonly UpdateAlbumTask $updateAlbumTask,
        private readonly UploadAlbumCoverTask $uploadAlbumCoverTask,
        private readonly SyncArtistsForAlbumTask $syncArtistsForAlbumTask,
        private readonly SyncAlbumsForArtistTask $syncAlbumsForArtistTask,
        private readonly FindTrackByPathTask $findTrackByPathTask,
        private readonly CreateTrackTask $createTrackTask,
        private readonly UpdateTrackTask $updateTrackTask,
        private readonly SyncArtistsForTrackTask $syncArtistsForTrackTask,
        private readonly ListTagsShortTask $listTagsShortTask,
        private readonly SyncTagsTask $syncTagsTask,
    ) {
    }

    public function run(MusicUpload $upload, array $tree, int $userId): array
    {
        return DB::transaction(function () use ($upload, $tree, $userId) {
            $tags = $this->listTagsShortTask->run() ?? [];
            $counters = [
                'artists_created' => 0,
                'albums_created' => 0,
                'albums_updated' => 0,
                'tracks_created' => 0,
                'tracks_updated' => 0,
                'tracks_skipped' => 0,
                'tracks_failed' => 0,
            ];

            /** @var array<string, Artist> $artistCache */
            $artistCache = [];
            $folderPath = (string) $tree['path'];
            $folderArtistName = trim((string) $tree['name']);
            $cover = $this->firstAlbumCover($tree);
            $processed = 0;
            $total = $this->countTracks($tree);
            $albumIds = [];

            $albums = $tree['albums'];
            usort($albums, static function (array $left, array $right): int {
                $leftRoot = empty($left['original_album']) ? 0 : 1;
                $rightRoot = empty($right['original_album']) ? 0 : 1;

                return $leftRoot <=> $rightRoot;
            });

            foreach ($albums as $albumData) {
                $albumArtists = $this->resolveAlbumArtists(
                    $albumData,
                    $userId,
                    $folderPath,
                    $folderArtistName,
                    $cover,
                    $artistCache,
                    $counters,
                );
                $primaryArtist = $albumArtists[0];
                $album = $this->persistAlbum($primaryArtist, $albumArtists, $albumData, $userId, $counters);
                $albumIds[] = $album->id;

                foreach ($albumData['tracks'] as $trackDto) {
                    $processed++;
                    $trackArtist = $this->artistFromCache(
                        $trackDto->artist,
                        $userId,
                        $folderPath,
                        $folderArtistName,
                        $cover,
                        $artistCache,
                        $counters,
                    );
                    $this->persistTrack($upload, $trackArtist, $album, $trackDto, $tags, $counters);
                    event(new UploadProgressed($upload, 'persisting', $processed, $total, $trackDto->title));
                }
            }

            $upload->artists()->sync(array_values(array_unique(array_map(
                static fn (Artist $artist) => $artist->id,
                $artistCache,
            ))));
            $upload->albums()->sync(array_values(array_unique($albumIds)));

            return $counters;
        });
    }

    /**
     * @param array<string, Artist> $artistCache
     * @return Artist[]
     */
    private function resolveAlbumArtists(
        array $albumData,
        int $userId,
        string $folderPath,
        string $folderArtistName,
        ?string $cover,
        array &$artistCache,
        array &$counters,
    ): array {
        $names = $albumData['artists'] ?? [];
        if ($names === []) {
            $names = [];
            foreach ($albumData['tracks'] as $trackDto) {
                $name = trim((string) $trackDto->artist);
                if ($name !== '') {
                    $names[$name] = $name;
                }
            }
            $names = array_values($names);
        }

        if ($names === []) {
            $names = [$folderArtistName];
        }

        $artists = [];
        foreach ($names as $name) {
            $artists[] = $this->artistFromCache(
                $name,
                $userId,
                $folderPath,
                $folderArtistName,
                $cover,
                $artistCache,
                $counters,
            );
        }

        return $artists;
    }

    /**
     * @param array<string, Artist> $artistCache
     */
    private function artistFromCache(
        string $name,
        int $userId,
        string $folderPath,
        string $folderArtistName,
        ?string $cover,
        array &$artistCache,
        array &$counters,
    ): Artist {
        $name = trim($name);
        if ($name === '') {
            $name = $folderArtistName;
        }

        if (!isset($artistCache[$name])) {
            $artistCache[$name] = $this->persistNamedArtist(
                $name,
                $userId,
                $folderPath,
                $folderArtistName,
                $cover,
                $counters,
            );
        }

        return $artistCache[$name];
    }

    private function persistNamedArtist(
        string $name,
        int $userId,
        string $folderPath,
        string $folderArtistName,
        ?string $cover,
        array &$counters,
    ): Artist {
        $isFolderArtist = strcasecmp($name, $folderArtistName) === 0;
        $artist = $this->findArtistByNameTask->run($name)
            ?? ($isFolderArtist ? $this->findArtistByPathTask->run($folderPath) : null);

        $path = $isFolderArtist ? $folderPath : $folderPath . ' :: ' . $name;

        if (!$artist) {
            $artist = $this->createArtistTask->run(CreateArtistDto::from([
                'user_id' => $userId,
                'name' => $name,
                'path' => $path,
            ]));
            $counters['artists_created']++;
        } elseif ($isFolderArtist && ($artist->path !== $folderPath || $artist->name !== $name)) {
            $artist = $this->updateArtistTask->run($artist, UpdateArtistDto::from([
                'user_id' => $userId,
                'name' => $name,
                'path' => $folderPath,
            ]));
        }

        $assignCover = $cover && is_file($cover) && empty($artist->image) && $isFolderArtist;

        if ($assignCover) {
            $artist->image = $this->uploadArtistCoverTask->run(new File($cover), (string) $artist->id);
            $artist->save();
        }

        return $artist;
    }

    /**
     * @param Artist[] $albumArtists
     */
    private function persistAlbum(
        Artist $primaryArtist,
        array $albumArtists,
        array $albumData,
        int $userId,
        array &$counters,
    ): Album {
        $parentId = null;
        if (!empty($albumData['original_album'])) {
            $parentId = $this->listAlbumsByNameTask->run(
                $primaryArtist,
                $albumData['original_album'],
                isset($albumData['album_type_id']) ? (int) $albumData['album_type_id'] : null,
            )?->id;
        }

        $payload = [
            'name' => $albumData['name'],
            'date' => $albumData['date'],
            'path' => $albumData['path'],
            'album_type_id' => $albumData['album_type_id'],
            'parent_id' => $parentId,
            'edition' => $albumData['edition'] ?? $albumData['attributes'] ?? null,
        ];

        $album = $this->findAlbumByPathTask->run($albumData['path']);

        if (!$album) {
            $album = $this->createAlbumTask->run(CreateAlbumDto::from([
                'user_id' => $userId,
                'name' => $payload['name'],
                'path' => $payload['path'],
                'album_type_id' => $payload['album_type_id'],
                'parent_id' => $payload['parent_id'],
                'edition' => $payload['edition'],
                'is_date_verified' => false,
            ]));

            $createExtras = array_filter([
                'date' => $payload['date'],
                'edition' => $payload['edition'],
            ], static fn (mixed $value) => $value !== null && $value !== '');

            if ($createExtras !== []) {
                $album = $this->updateAlbumTask->run($album, UpdateAlbumDto::from($createExtras));
            }
            $counters['albums_created']++;
        } elseif ($this->albumChanged($album, $payload)) {
            $album = $this->updateAlbumTask->run($album, UpdateAlbumDto::from($payload));
            $counters['albums_updated']++;
        }

        $artistIds = array_values(array_unique(array_map(static fn (Artist $artist) => $artist->id, $albumArtists)));
        $this->syncArtistsForAlbumTask->run($album, $artistIds);
        foreach ($albumArtists as $artist) {
            $this->syncAlbumsForArtistTask->run($artist, [$album->id]);
        }

        if (!empty($albumData['image']) && is_file($albumData['image']) && empty($album->image)) {
            $album->image = $this->uploadAlbumCoverTask->run(new File($albumData['image']), (int) $primaryArtist->id);
            $album->save();
        }

        $this->attachOrphanVersions($primaryArtist, $album);

        return $album;
    }

    private function attachOrphanVersions(Artist $artist, Album $root): void
    {
        if ($root->parent_id !== null || filled($root->edition)) {
            return;
        }

        $artist->albums()
            ->where('id', '!=', $root->id)
            ->whereNull('parent_id')
            ->where('name', $root->name)
            ->where('album_type_id', $root->album_type_id)
            ->whereNotNull('edition')
            ->where('edition', '!=', '')
            ->update(['parent_id' => $root->id]);
    }

    private function persistTrack(
        MusicUpload $upload,
        Artist $artist,
        Album $album,
        ParsedTrackDto $trackDto,
        array $tags,
        array &$counters,
    ): void {
        try {
            $existing = $this->findTrackByPathTask->run($trackDto->windows_path);
            $cd = (string) $trackDto->disc_number;
            $snapshot = $this->snapshot($trackDto);

            if ($existing && $this->trackUnchanged($existing, $album, $trackDto, $cd)) {
                $this->syncArtistsForTrackTask->run($existing, [$artist->id]);
                $this->musicUploadRepository->addTrackLog(
                    $upload,
                    UploadTrackStatusEnum::Skipped,
                    $trackDto->windows_path,
                    $existing->id,
                    $album->name,
                    $existing->name,
                    $snapshot,
                    null,
                    $album->id,
                    $artist->id,
                );
                $counters['tracks_skipped']++;
                return;
            }

            if ($existing) {
                $track = $this->updateTrackTask->run($existing, UpdateTrackDto::from([
                    'album_id' => $album->id,
                    'name' => $trackDto->title,
                    'number' => $trackDto->track_number ?: null,
                    'cd' => $cd,
                    'path' => $trackDto->windows_path,
                    'duration' => $trackDto->duration,
                    'bitrate' => $trackDto->bitrate,
                    'image' => $album->image,
                ]));
                $status = UploadTrackStatusEnum::Updated;
                $counters['tracks_updated']++;
            } else {
                $track = $this->createTrackTask->run($album, CreateTrackDto::from([
                    'user_id' => $upload->user_id,
                    'name' => $trackDto->title,
                    'number' => $trackDto->track_number ?: null,
                    'cd' => $cd,
                    'path' => $trackDto->windows_path,
                    'duration' => $trackDto->duration,
                    'bitrate' => $trackDto->bitrate,
                    'image' => $album->image,
                ]));
                $status = UploadTrackStatusEnum::Created;
                $counters['tracks_created']++;
            }

            $this->syncArtistsForTrackTask->run($track, [$artist->id]);
            $this->syncGenre($track, $trackDto->genre, $tags, $status === UploadTrackStatusEnum::Created);

            $this->musicUploadRepository->addTrackLog(
                $upload,
                $status,
                $trackDto->windows_path,
                $track->id,
                $album->name,
                $track->name,
                $snapshot,
                null,
                $album->id,
                $artist->id,
            );
        } catch (\Throwable $exception) {
            $counters['tracks_failed']++;
            $this->musicUploadRepository->addTrackLog(
                $upload,
                UploadTrackStatusEnum::Failed,
                $trackDto->windows_path,
                null,
                $album->name,
                $trackDto->title,
                $this->snapshot($trackDto),
                $exception->getMessage(),
                $album->id,
                $artist->id,
            );
        }
    }

    private function albumChanged(Album $album, array $payload): bool
    {
        $existingDate = $album->date?->format('Y-m-d');

        return $album->name !== $payload['name']
            || $existingDate !== $payload['date']
            || $album->path !== $payload['path']
            || (int) $album->album_type_id !== (int) $payload['album_type_id']
            || $album->parent_id !== $payload['parent_id']
            || $album->edition !== $payload['edition'];
    }

    private function trackUnchanged(Track $track, Album $album, ParsedTrackDto $dto, string $cd): bool
    {
        return (int) $track->album_id === (int) $album->id
            && $track->name === $dto->title
            && (int) $track->number === (int) $dto->track_number
            && (string) $track->cd === $cd
            && $track->getRawOriginal('duration') === $dto->duration
            && (int) $track->bitrate === (int) $dto->bitrate
            && $track->path === $dto->windows_path;
    }

    private function syncGenre(Track $track, ?string $genre, array $tags, bool $isNew): void
    {
        if (!$isNew || !$genre || !array_key_exists($genre, $tags)) {
            return;
        }

        $dto = SyncTagsDto::from(['tags' => [$tags[$genre]]]);
        $this->syncTagsTask->run($track, $dto);
    }

    private function snapshot(ParsedTrackDto $dto): array
    {
        return [
            'title' => $dto->title,
            'album' => $dto->album,
            'artist' => $dto->artist,
            'genre' => $dto->genre,
            'year' => $dto->year,
            'track_number' => $dto->track_number,
            'disc_number' => $dto->disc_number,
            'duration' => $dto->duration,
            'bitrate' => $dto->bitrate,
            'path' => $dto->windows_path,
        ];
    }

    private function firstAlbumCover(array $tree): ?string
    {
        foreach ($tree['albums'] as $album) {
            if (!empty($album['image'])) {
                return $album['image'];
            }
        }

        return null;
    }

    private function countTracks(array $tree): int
    {
        $total = 0;
        foreach ($tree['albums'] as $album) {
            $total += count($album['tracks']);
        }

        return $total;
    }
}
