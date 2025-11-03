<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Listeners;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\UI\Actions\ActivityLogCreateAction;
use App\Containers\LifelogSection\Post\Events\AttachmentAddedEvent as PostAttachmentAddedEvent;
use App\Containers\LifelogSection\Post\Events\AttachmentRemovedEvent as PostAttachmentRemovedEvent;
use App\Containers\LifelogSection\Post\Events\CreatedEvent as PostCreatedEvent;
use App\Containers\LifelogSection\Post\Events\DeletedEvent as PostDeletedEvent;
use App\Containers\LifelogSection\Post\Events\PostEvent;
use App\Containers\LifelogSection\Post\Events\TagAddedEvent as PostTagAddedEvent;
use App\Containers\LifelogSection\Post\Events\TagRemovedEvent as PostTagRemovedEvent;
use App\Containers\LifelogSection\Post\Events\UpdatedEvent as PostUpdatedEvent;
use App\Ship\Helpers\Correlation;
use Illuminate\Events\Dispatcher;

class PostEventsSubscriber
{
    public function subscribe(Dispatcher $events): void
    {
        $events->listen([
            PostCreatedEvent::class,
            PostUpdatedEvent::class,
            PostDeletedEvent::class,
            PostTagAddedEvent::class,
            PostTagRemovedEvent::class,
            PostAttachmentAddedEvent::class,
            PostAttachmentRemovedEvent::class
        ], $this->handlePostEvents(...));
    }

    private function handlePostEvents(PostEvent $event): void
    {
        $uuid = Correlation::get();
        $post = $event->getPost();
        $eventType = $event->getEventType();
        $userId = auth()?->user()?->id;

        $postLogDto = SystemLogCreateDto::from([
            'user_id' => $userId,
            'event_type' => $eventType,
            'loggable_type' => $post->getLoggableType(),
            'loggable_id' => $post->id,
            'correlation_uuid' => $uuid,
            'metadata' => $post->only(['title', 'content']),
        ]);

        ActivityLogCreateAction::dispatchSync($postLogDto);
    }
}
