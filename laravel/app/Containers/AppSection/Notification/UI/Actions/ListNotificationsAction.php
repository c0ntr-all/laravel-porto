<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\UI\Actions;

use App\Containers\AppSection\Notification\Data\DTO\NotificationListDto;
use App\Containers\AppSection\Notification\Tasks\ListUserNotificationsTask;
use App\Containers\AppSection\Notification\UI\API\Requests\ListNotificationsRequest;
use App\Containers\AppSection\Notification\UI\API\Transformers\UserNotificationTransformer;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Fractal\Pagination\Cursor;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;

class ListNotificationsAction extends BaseAction
{
    public function __construct(
        private readonly ListUserNotificationsTask $listUserNotificationsTask,
    ) {
    }

    public function handle(NotificationListDto $dto): LengthAwarePaginator
    {
        return $this->listUserNotificationsTask->run($dto);
    }

    public function asController(ListNotificationsRequest $request): JsonResponse
    {
        $validated = $request->validated();

        $dto = NotificationListDto::from([
            'user_id' => (int) auth()->id(),
            'unread_only' => array_key_exists('unread_only', $validated)
                ? $request->boolean('unread_only')
                : null,
            'per_page' => (int) ($validated['per_page'] ?? 20),
            'page' => (int) ($validated['page'] ?? 1),
            'cursor' => $validated['cursor'] ?? null,
        ]);

        if (is_string($dto->cursor) && $dto->cursor !== '') {
            $notifications = $this->listUserNotificationsTask->runCursor($dto);

            return fractal($notifications, new UserNotificationTransformer())
                ->withResourceName('notifications')
                ->withCursor($this->cursorFromPaginator($notifications))
                ->addMeta($this->cursorMeta($notifications))
                ->respond(200, [], JSON_PRETTY_PRINT);
        }

        $notifications = $this->handle($dto);

        return fractal($notifications, new UserNotificationTransformer())
            ->withResourceName('notifications')
            ->paginateWith(new IlluminatePaginatorAdapter($notifications))
            ->addMeta($this->pageMeta($notifications))
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
