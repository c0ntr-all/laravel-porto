<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Track\UI\Actions;

use App\Containers\MusicSection\Album\Data\Repositories\AlbumRepository;
use App\Containers\MusicSection\Album\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Album\UI\API\Transformers\AlbumTransformer;
use App\Containers\MusicSection\Track\Data\Repositories\TrackRepository;
use App\Containers\MusicSection\Track\UI\API\Transformers\TrackTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListTracksAction
{
    use AsAction;

    public function __construct(
        private readonly TrackRepository $trackRepository
    )
    {
    }

    public function handle(): CursorPaginator
    {
        return $this->trackRepository->getWithCursor();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $tracks = $this->handle();

        return fractal($tracks, new TrackTransformer())
            ->withResourceName('tracks')
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
