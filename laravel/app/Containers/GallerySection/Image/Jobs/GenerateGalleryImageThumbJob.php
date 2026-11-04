<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\Jobs;

use App\Containers\GallerySection\Image\Enums\ImageThumbTypeEnum;
use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Services\GalleryThumbnailWriter;
use App\Containers\GallerySection\Image\Services\PathGenerationService;
use App\Containers\GallerySection\Image\Tasks\ResolveImageFilePathTask;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class GenerateGalleryImageThumbJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $timeout = 120;
    public int $tries = 2;

    public function __construct(
        public readonly int $imageId,
    ) {
    }

    public function handle(
        GalleryThumbnailWriter $writer,
        ResolveImageFilePathTask $resolveImageFilePathTask,
        PathGenerationService $pathGenerationService,
    ): void {
        $image = Image::query()->find($this->imageId);
        if ($image === null || $image->thumb_status === 'ready') {
            return;
        }

        try {
            $source = $resolveImageFilePathTask->run($image);
        } catch (NotFoundHttpException) {
            $image->forceFill([
                'file_missing' => true,
                'thumb_status' => 'failed',
            ])->save();

            return;
        }

        [$width, $height] = ImageThumbTypeEnum::LIST->getSize();
        $info = @getimagesize($source);
        if (is_array($info) && (int) $info[1] > (int) $info[0]) {
            [$width, $height] = [$height, $width];
        }

        $relative = $image->relativePath('list_thumb');
        $disk = Storage::disk($pathGenerationService->diskName());
        $pathGenerationService->prepareFolder(dirname(str_replace('\\', '/', $relative)));

        try {
            $writer->write($source, $disk->path($relative), $width, $height);
        } catch (\Throwable $exception) {
            Log::warning("Unable to create list thumbnail({$image->uuid}): " . $exception->getMessage());
            $image->forceFill(['thumb_status' => 'failed'])->save();
            throw $exception;
        }

        $image->forceFill([
            'file_missing' => false,
            'thumb_status' => 'ready',
        ])->save();
    }
}
