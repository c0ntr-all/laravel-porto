<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Data\Repositories\MusicUploadRepository;
use App\Containers\MusicSection\Upload\Enums\UploadStatusEnum;
use App\Containers\MusicSection\Upload\Events\UploadFinished;
use App\Containers\MusicSection\Upload\Events\UploadProgressed;
use App\Containers\MusicSection\Upload\Events\UploadStarted;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Throwable;

class ImportArtistMusicTask extends ParentTask
{
    public function __construct(
        private readonly MusicUploadRepository $musicUploadRepository,
        private readonly ParseArtistFolderTask $parseArtistFolderTask,
        private readonly PersistLibraryTask $persistLibraryTask,
    ) {
    }

    public function run(MusicUpload $upload): MusicUpload
    {
        $upload = $this->musicUploadRepository->markRunning($upload);
        event(new UploadStarted($upload));

        $startedAt = microtime(true);

        try {
            $parsed = $this->parseArtistFolderTask->run($upload->source_path, $upload);
            $tree = $parsed['tree'];
            $tracksTotal = (int) $parsed['tracks_found'];
            $albumsTotal = count($tree['albums'] ?? []);
            $artistsTotal = $this->countArtists($tree);

            $upload->update([
                'tracks_found' => $tracksTotal,
                'artist_name' => trim((string) ($tree['name'] ?? '')) ?: $upload->artist_name,
                'meta' => array_merge($upload->meta ?? [], [
                    'albums_total' => $albumsTotal,
                    'artists_total' => $artistsTotal,
                ]),
            ]);
            $upload->refresh();

            event(new UploadProgressed(
                $upload,
                'scanned',
                0,
                $tracksTotal,
                '',
                [
                    'tracks_processed' => 0,
                    'tracks_total' => $tracksTotal,
                    'albums_processed' => 0,
                    'albums_total' => $albumsTotal,
                    'artists_processed' => 0,
                    'artists_total' => $artistsTotal,
                ],
            ));

            foreach ($parsed['errors'] as $error) {
                $this->musicUploadRepository->addTrackLog(
                    $upload,
                    \App\Containers\MusicSection\Upload\Enums\UploadTrackStatusEnum::Failed,
                    $error['path'],
                    null,
                    null,
                    null,
                    null,
                    $error['message'],
                );
            }

            $counters = $this->persistLibraryTask->run($upload, $parsed['tree'], (int) $upload->user_id);
            $upload->refresh();

            $failed = $counters['tracks_failed'] + count($parsed['errors']);
            $status = $failed > 0 ? UploadStatusEnum::CompletedWithErrors : UploadStatusEnum::Completed;

            $upload = $this->musicUploadRepository->finalize($upload, [
                'status' => $status,
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'tracks_found' => $parsed['tracks_found'],
                'tracks_created' => $counters['tracks_created'],
                'tracks_updated' => $counters['tracks_updated'],
                'tracks_skipped' => $counters['tracks_skipped'],
                'tracks_failed' => $failed,
                'albums_created' => $counters['albums_created'],
                'albums_updated' => $counters['albums_updated'],
                'artists_created' => $counters['artists_created'],
                'error_message' => null,
                'meta' => array_merge($upload->meta ?? [], [
                    'parse_errors' => count($parsed['errors']),
                    'albums_total' => $albumsTotal,
                    'artists_total' => $artistsTotal,
                    'imported_artists' => data_get($upload->meta, 'imported_artists', []),
                ]),
            ]);
        } catch (Throwable $exception) {
            $upload = $this->musicUploadRepository->finalize($upload, [
                'status' => UploadStatusEnum::Failed,
                'finished_at' => now(),
                'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
                'error_message' => $exception->getMessage(),
            ]);

            event(new UploadFinished($upload));

            throw $exception;
        }

        event(new UploadFinished($upload));

        return $upload;
    }

    /**
     * @param array{albums?: list<array{artists?: list<string>, tracks?: list<mixed>}>} $tree
     */
    private function countArtists(array $tree): int
    {
        $names = [];

        foreach ($tree['albums'] ?? [] as $album) {
            foreach ($album['artists'] ?? [] as $name) {
                $name = trim((string) $name);
                if ($name !== '') {
                    $names[$name] = true;
                }
            }

            foreach ($album['tracks'] ?? [] as $track) {
                $name = trim((string) ($track->artist ?? ''));
                if ($name !== '') {
                    $names[$name] = true;
                }
            }
        }

        if ($names === [] && filled($tree['name'] ?? null)) {
            return 1;
        }

        return count($names);
    }
}
