<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveVideoFilePathTask extends ParentTask
{
    public function run(Video $video): string
    {
        return match ($video->source) {
            FileSourceEnum::DEVICE->value => $this->devicePath($video),
            FileSourceEnum::WINDOWS->value => $this->windowsPath($video),
            default => throw new NotFoundHttpException('Video is not stored as a local file.'),
        };
    }

    private function devicePath(Video $video): string
    {
        $disk = Storage::disk((string) config('filesystems.default'));
        $absolute = $disk->path($video->relativePath('base'));

        if (!is_file($absolute)) {
            throw new NotFoundHttpException('Video file was not found on disk.');
        }

        return $absolute;
    }

    private function windowsPath(Video $video): string
    {
        if ($video->external_url === null || trim($video->external_url) === '') {
            throw new NotFoundHttpException('Video has no local file path.');
        }

        $disk = (string) config('video.windows.disk', 'windows_f');
        $root = (string) config('video.windows.root_folder');

        try {
            return WindowsPathHelper::assertReadableFile($video->external_url, $disk, $root);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException('Video file was not found on disk.');
        }
    }
}
