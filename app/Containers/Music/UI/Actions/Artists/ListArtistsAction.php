<?php declare(strict_types=1);

namespace App\Containers\Music\UI\Actions\Artists;

use App\Containers\Music\Data\Repositories\ArtistRepository;
use App\Containers\Music\UI\API\Requests\Artists\IndexRequest;
use App\Containers\Music\UI\API\Resources\Artists\List\ArtistCollection;
use Illuminate\Http\Response;
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

    public function asController(IndexRequest $request): Response
    {
        $data = $this->handle();

        return response(new ArtistCollection($data));
    }
}
