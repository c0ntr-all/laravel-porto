<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Listeners;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\UI\Actions\SystemLogCreateAction;
use App\Containers\AppSection\Attachment\Events\AttachedEvent as AttachmentAttachedEvent;
use App\Containers\AppSection\Attachment\Events\AttachmentEvent;
use App\Containers\AppSection\Attachment\Events\DetachedEvent as AttachmentDetachedEvent;
use App\Containers\AppSection\Tag\Events\AttachedEvent as TagAttachedEvent;
use App\Containers\AppSection\Tag\Events\CreatedEvent as TagCreatedEvent;
use App\Containers\AppSection\Tag\Events\DeletedEvent as TagDeletedEvent;
use App\Containers\AppSection\Tag\Events\DetachedEvent as TagDetachedEvent;
use App\Containers\AppSection\Tag\Events\TagEvent;
use App\Containers\AppSection\Tag\Events\TaggableEvent;
use App\Containers\AppSection\Tag\Events\UpdatedEvent as TagUpdatedEvent;
use App\Containers\GallerySection\Image\Events\CreatedEvent as GalleryImageCreatedEvent;
use App\Containers\GallerySection\Image\Events\DeletedEvent as GalleryImageDeletedEvent;
use App\Containers\GallerySection\Image\Events\GalleryImageEvent;
use App\Containers\GallerySection\Image\Events\UpdatedEvent as GalleryImageUpdatedEvent;
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

        // CRUD gallery_images
        $events->listen([
            GalleryImageCreatedEvent::class,
            GalleryImageDeletedEvent::class,
            GalleryImageUpdatedEvent::class,
        ], $this->handleGalleryImageEvents(...));

        // attaching/detaching attachments
        $events->listen([
            AttachmentAttachedEvent::class,
            AttachmentDetachedEvent::class,
        ], $this->handleAttachmentsEvents(...));
    }

    private function handlePostEvents(PostEvent $event): void
    {
        $uuid = Correlation::getUuid();
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
        $uuid = Correlation::getUuid();
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
        $uuid = Correlation::getUuid();
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
            'metadata' => ['name' => $taggable->tag->name]
        ]);

        SystemLogCreateAction::dispatchSync($systemLogDto);
    }

    public function handleAttachmentsEvents(AttachmentEvent $event): void
    {
        $uuid = Correlation::getUuid();
        $attachment = $event->getAttachment();
        $eventType = $event->getEventType();
        $userId = auth()?->user()?->id;

        $systemLogDto = SystemLogCreateDto::from([
            'user_id' => $userId,
            'event_type' => $eventType,
            'main_type' => $attachment->attachable_type,
            'main_id' => $attachment->attachable_id,
            'related_type' => $attachment->getLoggableType(),
            'related_id' => $attachment->id,
            'correlation_uuid' => $uuid,
            'metadata' => ['fileable_type' => $attachment->fileable_type, 'fileable_id' => $attachment->fileable_id]
        ]);

        SystemLogCreateAction::dispatchSync($systemLogDto);
    }

    private function handleGalleryImageEvents(GalleryImageEvent $event): void
    {
        $uuid = Correlation::getUuid();
        $galleryImage = $event->getImage();
        $eventType = $event->getEventType();
        $userId = auth()?->user()?->id;
        $metadata = $galleryImage->only(['source']);

        $systemLogDto = SystemLogCreateDto::from([
            'user_id' => $userId,
            'event_type' => $eventType,
            'main_type' => $galleryImage->getLoggableType(),
            'main_id' => $galleryImage->id,
            'correlation_uuid' => $uuid,
            'metadata' => $metadata,
        ]);

        SystemLogCreateAction::dispatchSync($systemLogDto);
    }
}
