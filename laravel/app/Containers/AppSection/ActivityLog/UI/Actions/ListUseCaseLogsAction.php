<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\Actions;

use App\Containers\AppSection\ActivityLog\Tasks\ListUseCaseLogsTask;
use App\Containers\AppSection\ActivityLog\UI\API\Requests\ListRequest;
use App\Containers\AppSection\ActivityLog\UI\API\Transformers\UseCaseLogTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\JsonResponse;
use League\Fractal\Pagination\Cursor;

class ListUseCaseLogsAction extends BaseAction
{
    public function __construct(
        private readonly ListUseCaseLogsTask $listUseCaseLogsTask
    ) {
    }

    public function handle(int $perPage, ?string $cursor): CursorPaginator
    {
        return $this->listUseCaseLogsTask->run($perPage, $cursor);
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $perPage = (int) ($request->validated('per_page') ?? 20);
        $cursor = $request->validated('cursor');
        $logs = $this->handle($perPage, $cursor);

        $fractal = fractal($logs, new UseCaseLogTransformer())
            ->withResourceName('use-case-logs')
            ->withCursor($this->cursorFromPaginator($logs))
            ->addMeta($this->cursorMeta($logs));

        if ($request->filled('include')) {
            $fractal->parseIncludes(explode(',', (string) $request->query('include')));
        }

        return $fractal->respond(200, [], JSON_PRETTY_PRINT);
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
     *     prev_cursor: string|null
     * }
     */
    private function cursorMeta(CursorPaginator $paginator): array
    {
        return [
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'prev_cursor' => $paginator->previousCursor()?->encode(),
        ];
    }
}
