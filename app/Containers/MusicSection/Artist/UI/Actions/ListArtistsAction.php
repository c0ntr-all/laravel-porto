<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Artist\UI\Actions;

use App\Containers\MusicSection\Artist\Data\Repositories\ArtistRepository;
use App\Containers\MusicSection\Artist\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\Artist\UI\API\Resources\List\ArtistCollection;
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
