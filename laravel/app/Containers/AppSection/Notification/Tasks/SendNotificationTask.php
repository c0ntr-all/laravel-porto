<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Tasks;

use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use App\Containers\AppSection\Notification\Managers\NotificationChannelManager;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SendNotificationTask extends ParentTask
{
    public function __construct(
        private readonly NotificationChannelManager $channelManager
    ) {
    }

    /**
     * @param list<string>|null $channels
     */
    public function run(object $notifiable, OutgoingNotificationData $notification, ?array $channels = null): void
    {
        $this->channelManager->send(
            $notifiable,
            $notification,
            $channels ?? config('notifications.default_channels', ['email'])
        );
    }
}
