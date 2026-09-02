<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\Repositories\UserNotificationRepository;
use App\Containers\AppSection\Notification\Events\UserNotificationRead;
use App\Containers\AppSection\Notification\Models\UserNotification;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MarkNotificationAsReadTask extends ParentTask
{
    public function __construct(
        private readonly UserNotificationRepository $userNotificationRepository,
    ) {
    }

    public function run(string $notificationId, int $userId): UserNotification
    {
        $notification = $this->userNotificationRepository->findForUser($notificationId, $userId);

        if ($notification === null) {
            throw new NotFoundHttpException('Notification not found.');
        }

        $notification->markAsRead();
        $unreadCount = $this->userNotificationRepository->countUnread($userId);

        UserNotificationRead::dispatch($userId, $notification->id, $unreadCount);

        return $notification->refresh();
    }
}
