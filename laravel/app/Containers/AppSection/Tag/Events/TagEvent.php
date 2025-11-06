<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Containers\AppSection\Tag\Models\Tag;
use Illuminate\Queue\SerializesModels;

abstract class TagEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Tag $tag
    )
    {
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
