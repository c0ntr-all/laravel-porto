<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\UI\Actions;

use App\Containers\MovieSection\Folder\Models\Folder;
use App\Containers\MovieSection\Folder\Tasks\ListFolderMoviesTask;
use App\Containers\MovieSection\Folder\UI\API\Requests\ListFolderMoviesRequest;
use App\Containers\MovieSection\Folder\UI\API\Transformers\FolderMovieTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ListFolderMoviesAction extends BaseAction
{
    public function __construct(
        private readonly ListFolderMoviesTask $listFolderMoviesTask,
    ) {
    }

    public function asController(Folder $folder, ListFolderMoviesRequest $request): JsonResponse
    {
        abort_unless((int) $folder->user_id === (int) $request->user()->id, 404);

        $movies = $this->listFolderMoviesTask->run($folder);

        return fractal($movies, new FolderMovieTransformer())
            ->withResourceName(ContainerAliasEnum::MOVIE->value)
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
