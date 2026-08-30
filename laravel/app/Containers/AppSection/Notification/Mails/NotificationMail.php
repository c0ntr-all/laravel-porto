<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Mails;

use App\Containers\AppSection\Notification\Data\DTO\OutgoingNotificationData;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NotificationMail extends Mailable
{
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly OutgoingNotificationData $notification
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->notification->subject,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'notification::mail.notification',
            with: [
                'subject' => $this->notification->subject,
                'body' => $this->notification->body,
                'meta' => $this->notification->meta,
            ],
        );
    }
}
