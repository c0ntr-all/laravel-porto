<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\Repositories\UserNotificationRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CountUnreadNotificationsTask extends ParentTask
{
    public function __construct(
        private readonly UserNotificationRepository $userNotificationRepository,
    ) {
    }

    public function run(int $userId): int
    {
        return $this->userNotificationRepository->countUnread($userId);
    }
}
