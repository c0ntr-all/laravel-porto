<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\UI\Actions;

use App\Containers\LifelogSection\Post\Data\DTO\PostListDto;
use App\Containers\LifelogSection\Post\Tasks\ListPostsTask;
use App\Containers\LifelogSection\Post\UI\API\Requests\ListPostsRequest;
use App\Containers\LifelogSection\Post\UI\API\Transformers\PostTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use App\Ship\Parents\Actions\BaseAction;
use League\Fractal\Pagination\Cursor;

class ListPostsAction extends BaseAction
{

    public function __construct(
        private readonly ListPostsTask $listPostsTask
    )
    {
    }

    public function handle(PostListDto $dto): CursorPaginator
    {
        return $this->listPostsTask->run($dto);
    }

    public function asController(ListPostsRequest $request): JsonResponse
    {
        $dto = PostListDto::from($request->validated());
        $dto->user_id = auth()->user()->id;

        $posts = $this->handle($dto);

        $include = (string) $request->query('include', '');
        if (str_contains($include, 'customFields')) {
            $posts->getCollection()->load('customFields');
        }

        return fractal($posts, new PostTransformer($dto->user_id))
            ->parseIncludes(['user', 'tags', 'attachments', 'movies.genres', 'movies.countries'])
            ->withResourceName(ContainerAliasEnum::LL_POST->value)
            ->withCursor($this->cursorFromPaginator($posts))
            ->addMeta($this->cursorMeta($posts))
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function cursorFromPaginator(CursorPaginator $paginator): Cursor
    {
        return new Cursor(
            $paginator->cursor()?->encode(),
            $paginator->previousCursor()?->encode(),
            $paginator->nextCursor()?->encode(),
            $paginator->count(),
        );
    }

    /**
     * @return array{
     *     per_page: int,
     *     has_more: bool,
     *     next_cursor: string|null,
     *     prev_cursor: string|null,
     *     next_page_url: string|null,
     *     prev_page_url: string|null
     * }
     */
    private function cursorMeta(CursorPaginator $paginator): array
    {
        return [
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'prev_cursor' => $paginator->previousCursor()?->encode(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }
}
