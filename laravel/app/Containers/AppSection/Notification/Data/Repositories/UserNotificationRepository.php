<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Data\Repositories;

use App\Containers\AppSection\Notification\Models\UserNotification;
use Illuminate\Contracts\Pagination\CursorPaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;

class UserNotificationRepository
{
    public function create(array $data): UserNotification
    {
        return UserNotification::create($data);
    }

    public function findForUser(string $id, int $userId): ?UserNotification
    {
        return UserNotification::query()
            ->whereKey($id)
            ->where('user_id', $userId)
            ->first();
    }

    public function paginateForUser(int $userId, ?bool $unreadOnly, int $perPage, int $page): LengthAwarePaginator
    {
        return $this->baseQuery($userId, $unreadOnly)
            ->paginate($perPage, ['*'], 'page', $page)
            ->withQueryString();
    }

    public function paginateForUserCursor(int $userId, ?bool $unreadOnly, int $perPage, ?string $cursor): CursorPaginator
    {
        return $this->baseQuery($userId, $unreadOnly)
            ->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
    }

    private function baseQuery(int $userId, ?bool $unreadOnly): Builder
    {
        $query = UserNotification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($unreadOnly) {
            $query->whereNull('read_at');
        }

        return $query;
    }

    public function countUnread(int $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->count();
    }

    public function markAllAsRead(int $userId): int
    {
        return UserNotification::query()
            ->where('user_id', $userId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
