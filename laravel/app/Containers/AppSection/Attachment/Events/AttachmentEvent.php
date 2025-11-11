<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Events;

use App\Containers\AppSection\Attachment\Models\Attachment;
use Illuminate\Queue\SerializesModels;

abstract class AttachmentEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Attachment $attachment
    )
    {
    }

    public function getAttachment(): Attachment
    {
        return $this->attachment;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
