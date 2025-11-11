<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Events;

use App\Ship\Enums\EventTypesEnum;

class AttachedEvent extends AttachmentEvent
{
    protected string $eventType = EventTypesEnum::ATTACHED->value;
}
