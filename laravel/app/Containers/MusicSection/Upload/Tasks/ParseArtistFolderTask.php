<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Tasks;

use App\Containers\MusicSection\Upload\Data\DTO\ParsedTrackDto;
use App\Containers\MusicSection\Upload\Events\UploadProgressed;
use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Containers\MusicSection\Upload\Models\MusicUpload;
use App\Ship\Parents\Tasks\Task as ParentTask;
use RuntimeException;

class ParseArtistFolderTask extends ParentTask
{
    public function __construct(
        private readonly ScanAudioFilesTask $scanAudioFilesTask,
        private readonly ExtractId3TagsTask $extractId3TagsTask,
        private readonly BuildLibraryTreeTask $buildLibraryTreeTask,
    ) {
    }

    /**
     * @return array{tree: array, tracks_found: int, errors: array<int, array{path: string, message: string}>}
     */
    public function run(string $windowsPath, ?MusicUpload $upload = null): array
    {
        $linuxPath = PathHelper::toLinux($windowsPath);
        $artistName = PathHelper::basename($windowsPath);
        $files = $this->scanAudioFilesTask->run($linuxPath);

        if ($files === []) {
            throw new RuntimeException('No audio files found in the given folder.');
        }

        $tracks = [];
        $errors = [];
        $total = count($files);

        foreach ($files as $index => $file) {
            try {
                $tracks[] = $this->extractId3TagsTask->run($file, $artistName);
            } catch (\Throwable $exception) {
                $errors[] = [
                    'path' => PathHelper::toWindows($file),
                    'message' => $exception->getMessage(),
                ];
            }

            if ($upload) {
                event(new UploadProgressed(
                    $upload,
                    'parsing',
                    $index + 1,
                    $total,
                    basename($file),
                    [
                        'tracks_processed' => $index + 1,
                        'tracks_total' => $total,
                    ],
                ));
            }
        }

        if ($tracks === []) {
            throw new RuntimeException('No tracks with readable ID3 tags were found.');
        }

        return [
            'tree' => $this->buildLibraryTreeTask->run($tracks, $artistName, PathHelper::normalizeWindows($windowsPath)),
            'tracks_found' => $total,
            'errors' => $errors,
        ];
    }
}
