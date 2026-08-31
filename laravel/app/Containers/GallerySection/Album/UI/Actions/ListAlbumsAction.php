<?php declare(strict_types=1);

namespace App\Containers\GallerySection\Album\UI\Actions;

use App\Containers\GallerySection\Album\Tasks\ListAlbumsTask;
use App\Containers\GallerySection\Album\UI\API\Requests\ListAlbumsRequest;
use App\Containers\GallerySection\Album\UI\API\Transformers\AlbumTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\JsonResponse;

class ListAlbumsAction extends BaseAction
{
    public function __construct(
        private readonly ListAlbumsTask $listAlbumsTask
    ) {
    }

    /**
     * @param list<string> $with
     */
    public function handle(array $with = []): Collection
    {
        return $this->listAlbumsTask->run($with);
    }

    public function asController(ListAlbumsRequest $request): JsonResponse
    {
        $with = $this->relationsFromInclude((string) $request->query('include', ''));
        $albums = $this->handle($with);

        return fractal($albums, new AlbumTransformer())
            ->withResourceName('albums')
            ->addMeta(['count' => $albums->count()])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    /**
     * @return list<string>
     */
    private function relationsFromInclude(string $include): array
    {
        $with = [];

        if (str_contains($include, 'images')) {
            $with[] = 'images';
        }
        if (str_contains($include, 'videos')) {
            $with[] = 'videos';
        }
        if (str_contains($include, 'user')) {
            $with[] = 'user';
        }

        return $with;
    }
}
