<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\UI\Actions;

use App\Containers\MusicSection\History\Tasks\ListHistoryTask;
use App\Containers\MusicSection\History\UI\API\Requests\IndexRequest;
use App\Containers\MusicSection\History\UI\API\Transformers\HistoryTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListHistoryAction extends BaseAction
{
    public function __construct(
        private readonly ListHistoryTask $listHistoryTask
    ) {
    }

    public function handle(int $userId): CursorPaginator
    {
        return $this->listHistoryTask->run($userId);
    }

    public function asController(IndexRequest $request): JsonResponse
    {
        $history = $this->handle((int) auth()->id());

        return fractal($history, new HistoryTransformer())
            ->withResourceName('history')
            ->parseIncludes(['track', 'track.artists'])
            ->withCursor($this->cursorFromPaginator($history))
            ->addMeta($this->cursorMeta($history))
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
