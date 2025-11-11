<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Events;

use App\Ship\Enums\EventTypesEnum;

class DetachedEvent extends AttachmentEvent
{
    protected string $eventType = EventTypesEnum::DETACHED->value;
}
