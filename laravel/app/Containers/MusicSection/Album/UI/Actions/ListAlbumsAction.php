<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Album\UI\Actions;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListAlbumsAction
{
    use AsAction;

    public function __construct(
        private readonly AlbumRepository $albumRepository
    )
    {
    }

    public function handle(): CursorPaginator
    {
        return $this->albumRepository->getWithCursor();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $albums = $this->handle();

        return fractal($albums, new AlbumTransformer())
            ->withResourceName('albums')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
