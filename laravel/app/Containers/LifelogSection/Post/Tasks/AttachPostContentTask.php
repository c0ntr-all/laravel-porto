<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\LifelogSection\Post\Support\PostContentHandlerRegistry;
use App\Ship\Parents\Tasks\Task as ParentTask;

class AttachPostContentTask extends ParentTask
{
    public function __construct(
        private readonly PostContentHandlerRegistry $handlerRegistry,
    ) {
    }

    public function run(Post $post, PostContentAttachDto $dto): void
    {
        $handler = $this->handlerRegistry->for($dto->content_type);

        if (!$handler) {
            return;
        }

        $handler->attach($post, $dto);
    }
}
