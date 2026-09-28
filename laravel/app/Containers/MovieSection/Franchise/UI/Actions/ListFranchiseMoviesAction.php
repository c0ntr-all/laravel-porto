<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\Actions;

use App\Containers\MovieSection\Franchise\Models\Franchise;
use App\Containers\MovieSection\Franchise\Tasks\ListFranchiseMoviesTask;
use App\Containers\MovieSection\Franchise\UI\API\Requests\ListFranchiseMoviesRequest;
use App\Containers\MovieSection\Franchise\UI\API\Transformers\FranchiseMovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ListFranchiseMoviesAction extends BaseAction
{
    public function __construct(
        private readonly ListFranchiseMoviesTask $listFranchiseMoviesTask,
    ) {
    }

    public function asController(Franchise $franchise, ListFranchiseMoviesRequest $request): JsonResponse
    {
        $movies = $this->listFranchiseMoviesTask->run($franchise);

        return fractal($movies, new FranchiseMovieTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
            ->parseIncludes(['genres', 'countries'])
            ->paginateWith(new IlluminatePaginatorAdapter($movies))
            ->addMeta($this->pageMeta($movies))
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    /**
     * @return array{
     *     current_page: int,
     *     last_page: int,
     *     per_page: int,
     *     total: int
     * }
     */
    private function pageMeta(LengthAwarePaginator $paginator): array
    {
        return [
            'current_page' => $paginator->currentPage(),
            'last_page' => $paginator->lastPage(),
            'per_page' => $paginator->perPage(),
            'total' => $paginator->total(),
        ];
    }
}
