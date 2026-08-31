<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\Tasks\ResolveVideoFilePathTask;
use App\Containers\GallerySection\Video\UI\API\Requests\GetVideoFileRequest;
use App\Ship\Enums\FileSourceEnum;
use App\Ship\Parents\Actions\BaseAction;
use App\Ship\Tasks\StreamLocalFileTask;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class GetVideoFileAction extends BaseAction
{
    public function __construct(
        private readonly ResolveVideoFilePathTask $resolveVideoFilePathTask,
        private readonly StreamLocalFileTask $streamLocalFileTask,
    ) {
    }

    public function handle(Video $video): BinaryFileResponse|RedirectResponse
    {
        if ($video->source === FileSourceEnum::WEB->value && $video->external_url) {
            return redirect()->away($video->external_url);
        }

        $absolutePath = $this->resolveVideoFilePathTask->run($video);
        $filename = $video->original_name
            ?: basename(str_replace('\\', '/', $video->external_url ?: $absolutePath));

        return $this->streamLocalFileTask->run($absolutePath, $filename);
    }

    public function asController(Video $video, GetVideoFileRequest $request): BinaryFileResponse|RedirectResponse
    {
        return $this->handle($video);
    }
}
