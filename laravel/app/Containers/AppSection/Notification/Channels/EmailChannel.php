<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Channels;

use App\Containers\AppSection\Notification\Contracts\HasMailRoute;
use App\Containers\AppSection\Notification\Contracts\NotificationChannelInterface;
use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use App\Containers\AppSection\Notification\Enums\NotificationChannelEnum;
use App\Containers\AppSection\Notification\Mails\NotificationMail;
use Illuminate\Support\Facades\Mail;
use InvalidArgumentException;
use Illuminate\Notifications\Notifiable;

class EmailChannel implements NotificationChannelInterface
{
    public function key(): string
    {
        return NotificationChannelEnum::EMAIL->value;
    }

    public function send(object $notifiable, OutgoingNotificationData $notification): void
    {
        $email = $this->resolveEmail($notifiable);

        if ($email === null || $email === '') {
            throw new InvalidArgumentException('Notifiable does not provide a mail address.');
        }

        Mail::to($email)->send(new NotificationMail($notification));
    }

    private function resolveEmail(object $notifiable): ?string
    {
        if ($notifiable instanceof HasMailRoute) {
            return $notifiable->routeNotificationForMail();
        }

        if (method_exists($notifiable, 'routeNotificationForMail')) {
            return $notifiable->routeNotificationForMail();
        }

        if (property_exists($notifiable, 'email')) {
            return $notifiable->email;
        }

        // Laravel Notifiable trait support
        if (in_array(Notifiable::class, class_uses_recursive($notifiable), true)
            && method_exists($notifiable, 'routeNotificationFor')
        ) {
            return $notifiable->routeNotificationFor('mail');
        }

        return null;
    }
}
