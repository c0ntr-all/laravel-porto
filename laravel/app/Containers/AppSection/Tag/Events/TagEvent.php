<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Events;

use App\Containers\AppSection\Tag\Models\Tag;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class TagEvent extends DomainActivityEvent
{
    public function __construct(
        protected Tag $tag
    ) {
    }

    public function getTag(): Tag
    {
        return $this->tag;
    }

    public function activityMainType(): string
    {
        return $this->tag->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->tag->id;
    }

    public function activityMetadata(): array
    {
        return $this->tag->only(['user_id', 'name', 'slug', 'description']);
    }

    protected function activitySubject(): Model
    {
        return $this->tag;
    }
}
