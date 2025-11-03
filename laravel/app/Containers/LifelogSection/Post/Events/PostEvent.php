<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Events;

use App\Containers\LifelogSection\Post\Models\Post;
use Illuminate\Queue\SerializesModels;

abstract class PostEvent
{
    use SerializesModels;

    protected string $eventType = 'unknown';

    public function __construct(
        protected Post $post
    )
    {
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function getEventType(): string
    {
        return $this->eventType;
    }
}
