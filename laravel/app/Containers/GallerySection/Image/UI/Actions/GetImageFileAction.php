<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Image\UI\Actions;

use App\Containers\GallerySection\Image\Models\Image;
use App\Containers\GallerySection\Image\Tasks\ResolveImageFilePathTask;
use App\Containers\GallerySection\Image\UI\API\Requests\GetImageFileRequest;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\BaseAction;
use App\Ship\Tasks\StreamLocalFileTask;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GetImageFileAction extends BaseAction
{
    public function __construct(
        private readonly ResolveImageFilePathTask $resolveImageFilePathTask,
        private readonly StreamLocalFileTask $streamLocalFileTask,
    ) {
    }

    public function handle(Image $image): BinaryFileResponse|RedirectResponse
    {
        if ($image->source === FileSourceEnum::WEB->value && $image->external_url) {
            return redirect()->away($image->external_url);
        }

        $absolutePath = $this->resolveImageFilePathTask->run($image);
        $filename = basename(str_replace('\\', '/', $image->external_url ?: $absolutePath));

        return $this->streamLocalFileTask->run($absolutePath, $filename);
    }

    public function asController(Image $image, GetImageFileRequest $request): BinaryFileResponse|RedirectResponse
    {
        return $this->handle($image);
    }
}
