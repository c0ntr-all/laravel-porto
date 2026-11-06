<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\DTO\CreateUserNotificationDto;
use App\Containers\AppSection\Notification\Data\Repositories\UserNotificationRepository;
use App\Containers\AppSection\Notification\Events\UserNotificationCreated;
use App\Containers\AppSection\Notification\Models\UserNotification;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\Log;
use Throwable;

class CreateUserNotificationTask extends ParentTask
{
    public function __construct(
        private readonly UserNotificationRepository $userNotificationRepository,
    ) {
    }

    public function run(CreateUserNotificationDto $dto): UserNotification
    {
        $notification = $this->userNotificationRepository->create($dto->toArray());

        try {
            $unreadCount = $this->userNotificationRepository->countUnread($dto->user_id);
            UserNotificationCreated::dispatch($notification, $unreadCount);
        } catch (Throwable $exception) {
            Log::warning('notification.broadcast_failed', [
                'user_notification_id' => $notification->id,
                'user_id' => $notification->user_id,
                'message' => $exception->getMessage(),
            ]);
        }

        return $notification;
    }
}
