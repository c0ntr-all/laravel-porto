<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Data\Repositories;

use App\Containers\AppSection\Notification\Models\UserNotification;
use Illuminate\Contracts\Pagination\CursorPaginator;

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

    public function paginateForUser(int $userId, ?bool $unreadOnly, int $perPage, ?string $cursor): CursorPaginator
    {
        $query = UserNotification::query()
            ->where('user_id', $userId)
            ->orderByDesc('created_at')
            ->orderByDesc('id');

        if ($unreadOnly === true) {
            $query->whereNull('read_at');
        }

        return $query->cursorPaginate($perPage, ['*'], 'cursor', $cursor);
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
