<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Models\Image;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Storage;

class DeleteImageFilesTask extends ParentTask
{
    public function run(Image $image): void
    {
        $disk = Storage::disk((string) config('filesystems.default'));
        $paths = [
            $image->relativePath('list_thumb'),
            $image->relativePath('preview_thumb'),
        ];

        if ($image->source === FileSourceEnum::DEVICE->value) {
            $paths[] = $image->relativePath('base');
        }

        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
