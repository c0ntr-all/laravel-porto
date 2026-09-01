<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Tasks;

use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Storage;

class CopyImageFilesTask extends ParentTask
{
    public function __construct(
        private readonly PathGenerationService $pathGenerationService,
    ) {
    }

    public function run(Image $source, Image $destination): void
    {
        $disk = Storage::disk($this->pathGenerationService->diskName());
        $keys = ['list_thumb', 'preview_thumb'];

        if ($source->source === FileSourceEnum::DEVICE->value) {
            $keys[] = 'base';
        }

        foreach ($keys as $maskKey) {
            $from = $source->relativePath($maskKey);
            $to = $destination->relativePath($maskKey);

            if (!$disk->exists($from) || $disk->exists($to)) {
                continue;
            }

            $this->pathGenerationService->prepareFolder(dirname(str_replace('\\', '/', $to)));
            $disk->copy($from, $to);
        }
    }
}
