<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Containers\AppSection\Tag\Models\Taggable;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class TaggableEvent extends DomainActivityEvent
{
    public function __construct(
        protected Taggable $taggable
    ) {
    }

    public function getTaggable(): Taggable
    {
        return $this->taggable;
    }

    public function activityMainType(): string
    {
        return (string) $this->taggable->taggable_type;
    }

    public function activityMainId(): string
    {
        return (string) $this->taggable->taggable_id;
    }

    public function activityRelatedType(): ?string
    {
        return $this->taggable->getLoggableType();
    }

    public function activityRelatedId(): ?string
    {
        return (string) $this->taggable->tag_id;
    }

    public function activityMetadata(): array
    {
        return [
            'name' => $this->taggable->tag?->name,
        ];
    }

    protected function activitySubject(): Model
    {
        return $this->taggable;
    }

    protected function activitySubjectUserId(): int|string|null
    {
        return $this->taggable->tag?->user_id;
    }
}
