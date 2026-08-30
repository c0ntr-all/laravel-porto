<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Managers;

use App\Containers\AppSection\Notification\Contracts\NotificationChannelInterface;
use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use InvalidArgumentException;

class NotificationChannelManager
{
    /** @var array<string, NotificationChannelInterface> */
    private array $channels = [];

    public function register(NotificationChannelInterface $channel): void
    {
        $this->channels[$channel->key()] = $channel;
    }

    public function driver(string $channel): NotificationChannelInterface
    {
        if (!isset($this->channels[$channel])) {
            throw new InvalidArgumentException("Notification channel [{$channel}] is not registered.");
        }

        return $this->channels[$channel];
    }

    /**
     * @param list<string>|null $channels
     */
    public function send(object $notifiable, OutgoingNotificationData $notification, ?array $channels = null): void
    {
        $channels ??= array_keys($this->channels);

        foreach ($channels as $channel) {
            $this->driver($channel)->send($notifiable, $notification);
        }
    }

    /**
     * @return list<string>
     */
    public function registeredChannels(): array
    {
        return array_keys($this->channels);
    }
}
