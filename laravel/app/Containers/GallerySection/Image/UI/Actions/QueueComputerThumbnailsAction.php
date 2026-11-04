<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Enums\ImageMimeEnum;
use App\Containers\GallerySection\Image\Jobs\GenerateGalleryBrowseThumbJob;
use App\Containers\GallerySection\Image\Services\GalleryBrowseThumb;
use App\Containers\GallerySection\Image\UI\API\Requests\QueueComputerThumbnailsRequest;
use App\Ship\Helpers\WindowsPathHelper;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class QueueComputerThumbnailsAction extends BaseAction
{
    public function asController(QueueComputerThumbnailsRequest $request): JsonResponse
    {
        $diskName = (string) config('image.windows.disk', 'windows_f');
        $root = (string) config('image.windows.root_folder', config('app.windows_images_root_folder'));
        $public = Storage::disk((string) config('image.disk', 'public'));
        $items = [];
        $queued = [];

        foreach ($request->validated('paths') as $path) {
            $items[] = $this->item((string) $path, $diskName, $root, $public, $queued);
        }

        return response()->json([
            'data' => $items,
        ]);
    }

    /**
     * @param array<string, true> $queued
     * @return array{path: string, status: string, url: string|null}
     */
    private function item(string $path, string $diskName, string $root, Filesystem $public, array &$queued): array
    {
        try {
            $normalized = WindowsPathHelper::normalizeWindows($path);
        } catch (InvalidArgumentException) {
            return ['path' => $path, 'status' => 'missing', 'url' => null];
        }

        $extension = strtolower(pathinfo(str_replace('\\', '/', $normalized), PATHINFO_EXTENSION));
        if (
            !WindowsPathHelper::isUnderRoot($normalized, $root)
            || !in_array($extension, ImageMimeEnum::libraryExtensions(), true)
        ) {
            return ['path' => $normalized, 'status' => 'missing', 'url' => null];
        }

        try {
            $absolute = WindowsPathHelper::assertReadableFile($normalized, $diskName, $root);
        } catch (InvalidArgumentException) {
            return ['path' => $normalized, 'status' => 'missing', 'url' => null];
        }

        $size = (int) (@filesize($absolute) ?: 0);
        $mtime = (int) (@filemtime($absolute) ?: 0);
        $hash = GalleryBrowseThumb::hash($normalized, $size, $mtime);
        $relative = GalleryBrowseThumb::relativePath($hash);
        $url = GalleryBrowseThumb::url($hash);

        if ($public->exists($relative)) {
            return ['path' => $normalized, 'status' => 'ready', 'url' => $url];
        }

        if (!isset($queued[$hash])) {
            GenerateGalleryBrowseThumbJob::dispatch($normalized, $size, $mtime);
            $queued[$hash] = true;
        }

        return ['path' => $normalized, 'status' => 'pending', 'url' => null];
    }
}
