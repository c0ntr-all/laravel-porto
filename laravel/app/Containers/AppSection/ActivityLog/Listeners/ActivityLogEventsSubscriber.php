<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Listeners;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\UI\Actions\SystemLogCreateAction;
use App\Containers\AppSection\Tag\Events\AttachedEvent as TagAttachedEvent;
use App\Containers\AppSection\Tag\Events\CreatedEvent as TagCreatedEvent;
use App\Containers\AppSection\Tag\Events\DeletedEvent as TagDeletedEvent;
use App\Containers\AppSection\Tag\Events\DetachedEvent as TagDetachedEvent;
use App\Containers\AppSection\Tag\Events\TagEvent;
use App\Containers\AppSection\Tag\Events\TaggableEvent;
use App\Containers\AppSection\Tag\Events\UpdatedEvent as TagUpdatedEvent;
use App\Containers\LifelogSection\Post\Events\CreatedEvent as PostCreatedEvent;
use App\Containers\LifelogSection\Post\Events\DeletedEvent as PostDeletedEvent;
use App\Containers\LifelogSection\Post\Events\PostEvent;
use App\Containers\LifelogSection\Post\Events\UpdatedEvent as PostUpdatedEvent;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Helpers\Correlation;
use Illuminate\Events\Dispatcher;

class ActivityLogEventsSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        // CRUD posts
        $events->listen([
            PostCreatedEvent::class,
            PostUpdatedEvent::class,
            PostDeletedEvent::class
        ], $this->handlePostEvents(...));

        // CRUD tags
        $events->listen([
            TagCreatedEvent::class,
            TagDeletedEvent::class,
            TagUpdatedEvent::class,
        ], $this->handleTagEvents(...));

        // attaching/detaching tags
        $events->listen([
            TagAttachedEvent::class,
            TagDetachedEvent::class,
        ], $this->handleTaggableEvents(...));
    }

    private function handlePostEvents(PostEvent $event): void
    {
        $uuid = Correlation::get();
        $post = $event->getPost();
        $eventType = $event->getEventType();
        $userId = auth()?->user()?->id;
        $metadata = $post->only(['title', 'content']);

        if ($eventType === EventTypesEnum::UPDATED->value) {
            $metadata = $post->getChanges();
        }

        $systemLogDto = SystemLogCreateDto::from([
            'user_id' => $userId,
            'event_type' => $eventType,
            'main_type' => $post->getLoggableType(),
            'main_id' => $post->id,
            'correlation_uuid' => $uuid,
            'metadata' => $metadata,
        ]);

        SystemLogCreateAction::dispatchSync($systemLogDto);
    }

    public function handleTagEvents(TagEvent $event): void
    {
        $uuid = Correlation::get();
        $tag = $event->getTag();
        $eventType = $event->getEventType();
        $userId = auth()?->user()?->id;

        $systemLogDto = SystemLogCreateDto::from([
            'user_id' => $userId,
            'event_type' => $eventType,
            'main_type' => $tag->getLoggableType(),
            'main_id' => $tag->id,
            'correlation_uuid' => $uuid,
            'metadata' => $tag->only(['name', 'content']),
        ]);

        SystemLogCreateAction::dispatchSync($systemLogDto);
    }

    public function handleTaggableEvents(TaggableEvent $event): void
    {
        $uuid = Correlation::get();
        $taggable = $event->getTaggable();
        $eventType = $event->getEventType();
        $userId = auth()?->user()?->id;

        $systemLogDto = SystemLogCreateDto::from([
            'user_id' => $userId,
            'event_type' => $eventType,
            'main_type' => $taggable->taggable_type,
            'main_id' => $taggable->taggable_id,
            'related_type' => $taggable->getLoggableType(),
            'related_id' => $taggable->tag_id,
            'correlation_uuid' => $uuid,
            'metadata' => ['tag_id' => $taggable->tag_id]
        ]);

        SystemLogCreateAction::dispatchSync($systemLogDto);
    }
}
