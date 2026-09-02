<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\DTO\NotificationListDto;
use App\Containers\AppSection\Notification\Data\Repositories\UserNotificationRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Contracts\Pagination\CursorPaginator;

class ListUserNotificationsTask extends ParentTask
{
    public function __construct(
        private readonly UserNotificationRepository $userNotificationRepository,
    ) {
    }

    public function run(NotificationListDto $dto): CursorPaginator
    {
        return $this->userNotificationRepository->paginateForUser(
            $dto->user_id,
            $dto->unread_only,
            $dto->per_page,
            $dto->cursor,
        );
    }
}
