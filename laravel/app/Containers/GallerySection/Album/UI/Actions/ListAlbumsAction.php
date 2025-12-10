<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Tasks\ListAlbumsTask;
use App\Containers\GallerySection\Album\UI\API\Requests\ListAlbumsRequest;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAlbumsAction
{
    use AsAction;

    public function __construct(
        private readonly ListAlbumsTask $listAlbumsTask
    )
    {
    }

    public function handle(): Collection
    {
        return $this->listAlbumsTask->run();
    }

    public function asController(ListAlbumsRequest $request): JsonResponse
    {
        $albums = $this->handle();

        return fractal($albums, new AlbumTransformer())
            ->withResourceName('albums')
            ->addMeta(['count' => $albums->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
