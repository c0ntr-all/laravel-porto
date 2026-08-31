<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ResolveImageFilePathTask extends ParentTask
{
    public function run(Image $image): string
    {
        return match ($image->source) {
            FileSourceEnum::DEVICE->value => $this->devicePath($image),
            FileSourceEnum::WINDOWS->value => $this->windowsPath($image),
            default => throw new NotFoundHttpException('Image is not stored as a local file.'),
        };
    }

    private function devicePath(Image $image): string
    {
        $disk = Storage::disk((string) config('filesystems.default'));
        $relative = $image->relativePath('base');
        $absolute = $disk->path($relative);

        if (!is_file($absolute)) {
            throw new NotFoundHttpException('Image file was not found on disk.');
        }

        return $absolute;
    }

    private function windowsPath(Image $image): string
    {
        if ($image->external_url === null || trim($image->external_url) === '') {
            throw new NotFoundHttpException('Image has no local file path.');
        }

        $disk = (string) config('image.windows.disk', 'windows_f');
        $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));

        try {
            return WindowsPathHelper::assertReadableFile($image->external_url, $disk, $root);
        } catch (\InvalidArgumentException) {
            throw new NotFoundHttpException('Image file was not found on disk.');
        }
    }
}
