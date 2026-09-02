<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\Repositories\UserNotificationRepository;
use App\Containers\AppSection\Notification\Events\UserNotificationRead;
use App\Ship\Parents\Tasks\Task as ParentTask;

class MarkAllNotificationsAsReadTask extends ParentTask
{
    public function __construct(
        private readonly UserNotificationRepository $userNotificationRepository,
    ) {
    }

    public function run(int $userId): int
    {
        $updated = $this->userNotificationRepository->markAllAsRead($userId);
        $unreadCount = $this->userNotificationRepository->countUnread($userId);

        UserNotificationRead::dispatch($userId, '', $unreadCount, markAll: true);

        return $updated;
    }
}
