<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Data\Repositories\MusicUploadRepository;
use App\Containers\MusicSection\Upload\Enums\UploadStatusEnum;
use App\Containers\MusicSection\Upload\Events\UploadFinished;
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
                'meta' => [
                    'parse_errors' => count($parsed['errors']),
                ],
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
}
