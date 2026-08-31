<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Video\UI\Actions;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Video\Tasks\ListVideosTask;
use App\Containers\GallerySection\Video\UI\API\Requests\ListVideosRequest;
use App\Containers\GallerySection\Video\UI\API\Transformers\VideoTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListVideosAction extends BaseAction
{
    public function __construct(
        private readonly ListVideosTask $listVideosTask
    ) {
    }

    public function handle(Album $album): Collection
    {
        return $this->listVideosTask->run($album);
    }

    public function asController(Album $album, ListVideosRequest $request): JsonResponse
    {
        $videos = $this->handle($album);

        return fractal($videos, new VideoTransformer())
            ->withResourceName(ContainerAliasEnum::GALLERY_VIDEO->value)
            ->addMeta(['count' => $videos->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
