<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Events;

use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Events\DomainActivityEvent;
use Illuminate\Database\Eloquent\Model;

abstract class PostEvent extends DomainActivityEvent
{
    public function __construct(
        protected Post $post
    ) {
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function activityMainType(): string
    {
        return $this->post->getLoggableType();
    }

    public function activityMainId(): string
    {
        return (string) $this->post->id;
    }

    public function activityMetadata(): array
    {
        return $this->snapshot(['title', 'content']);
    }

    protected function activitySubject(): Model
    {
        return $this->post;
    }
}
