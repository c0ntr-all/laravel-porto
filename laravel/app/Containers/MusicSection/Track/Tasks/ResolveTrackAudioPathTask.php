<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\Tasks;

use App\Containers\MusicSection\Track\Models\Track;
use App\Containers\MusicSection\Upload\Helpers\PathHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveTrackAudioPathTask extends ParentTask
{
    public function run(Track $track): string
    {
        if ($track->path === null || trim($track->path) === '') {
            throw new NotFoundHttpException('Track has no local audio file.');
        }

        try {
            $linuxPath = PathHelper::toLinux($track->path);
        } catch (InvalidArgumentException $exception) {
            throw new NotFoundHttpException('Track path is outside of the music library.');
        }

        $realFile = realpath($linuxPath);
        $libraryRoot = realpath($this->libraryRoot());

        if (
            $realFile === false
            || $libraryRoot === false
            || !is_file($realFile)
            || !str_starts_with($realFile, rtrim($libraryRoot, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR)
        ) {
            throw new NotFoundHttpException('Audio file was not found on disk.');
        }

        return $realFile;
    }

    private function libraryRoot(): string
    {
        $disk = (string) config('music_upload.disk', 'windows_f');

        return Storage::disk($disk)->path('');
    }
}
