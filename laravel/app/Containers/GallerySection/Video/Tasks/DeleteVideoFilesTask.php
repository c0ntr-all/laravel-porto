<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\Tasks;

use App\Containers\GallerySection\Video\Models\Video;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Storage;

class DeleteVideoFilesTask extends ParentTask
{
    public function run(Video $video): void
    {
        $disk = Storage::disk((string) config('filesystems.default'));
        $paths = [
            $video->relativePath('list_thumb'),
        ];

        if ($video->source === FileSourceEnum::DEVICE->value) {
            $paths[] = $video->relativePath('base');
        }

        foreach ($paths as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
            }
        }
    }
}
