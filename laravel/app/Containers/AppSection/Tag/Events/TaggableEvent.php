<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Containers\AppSection\Tag\Models\Taggable;
use Illuminate\Queue\SerializesModels;

abstract class TaggableEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Taggable $taggable
    )
    {
    }

    public function getTaggable(): Taggable
    {
        return $this->taggable;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
