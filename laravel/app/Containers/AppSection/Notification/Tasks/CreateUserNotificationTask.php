<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\DTO\CreateUserNotificationDto;
use App\Containers\AppSection\Notification\Data\Repositories\UserNotificationRepository;
use App\Containers\AppSection\Notification\Events\UserNotificationCreated;
use App\Containers\AppSection\Notification\Models\UserNotification;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateUserNotificationTask extends ParentTask
{
    public function __construct(
        private readonly UserNotificationRepository $userNotificationRepository,
    ) {
    }

    public function run(CreateUserNotificationDto $dto): UserNotification
    {
        $notification = $this->userNotificationRepository->create($dto->toArray());
        $unreadCount = $this->userNotificationRepository->countUnread($dto->user_id);

        UserNotificationCreated::dispatch($notification, $unreadCount);

        return $notification;
    }
}
