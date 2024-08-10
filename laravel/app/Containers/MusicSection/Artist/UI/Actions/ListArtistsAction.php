<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Artist\UI\API\Transformers\ArtistTransformer;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use Lorisleiva\Actions\Concerns\AsAction;

class ListArtistsAction
{
    use AsAction;

    public function __construct(
        private readonly ArtistRepository $artistRepository
    )
    {
    }

    public function handle(): CursorPaginator
    {
        return $this->artistRepository->getWithCursor();
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $artists = $this->handle();

        return fractal($artists, new ArtistTransformer())
            ->withResourceName('artists')
            ->parseIncludes(['tags'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
