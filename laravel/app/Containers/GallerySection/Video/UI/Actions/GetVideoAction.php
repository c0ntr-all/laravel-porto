<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Video\Models\Video;
use App\Containers\GallerySection\Video\UI\API\Requests\GetVideoRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetVideoAction extends BaseAction
{
    public function handle(Video $video): Video
    {
        return $video;
    }

    public function asController(Video $video, GetVideoRequest $request): JsonResponse
    {
        $video = $this->handle($video);

        return fractal($video, new VideoTransformer())
            ->withResourceName('videos')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
