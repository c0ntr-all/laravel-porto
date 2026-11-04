<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Jobs;

use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Image\Services\GalleryBrowseThumb;
use App\Containers\GallerySection\Image\Services\GalleryThumbnailWriter;
use App\Ship\Helpers\WindowsPathHelper;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class GenerateGalleryBrowseThumbJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;
    public int $tries = 2;

    public function __construct(
        public readonly string $windowsPath,
        public readonly int $size,
        public readonly int $mtime,
    ) {
    }

    public function handle(GalleryThumbnailWriter $writer): void
    {
        $diskName = (string) config('image.windows.disk', 'windows_f');
        $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));

        try {
            $source = WindowsPathHelper::assertReadableFile($this->windowsPath, $diskName, $root);
        } catch (\InvalidArgumentException) {
            return;
        }

        $currentSize = (int) (@filesize($source) ?: 0);
        $currentMtime = (int) (@filemtime($source) ?: 0);
        if ($currentSize !== $this->size || $currentMtime !== $this->mtime) {
            return;
        }

        [$width, $height] = ImageThumbTypeEnum::LIST->getSize();
        $info = @getimagesize($source);
        if (is_array($info) && (int) $info[1] > (int) $info[0]) {
            [$width, $height] = [$height, $width];
        }

        $hash = GalleryBrowseThumb::hash($this->windowsPath, $this->size, $this->mtime);
        $relative = GalleryBrowseThumb::relativePath($hash);
        $disk = Storage::disk((string) config('image.disk', 'public'));
        $destination = $disk->path($relative);
        if (is_file($destination)) {
            return;
        }

        try {
            $writer->write($source, $destination, $width, $height);
        } catch (\Throwable $exception) {
            Log::warning('Unable to create browse thumbnail: ' . $exception->getMessage());
            throw $exception;
        }
    }
}
