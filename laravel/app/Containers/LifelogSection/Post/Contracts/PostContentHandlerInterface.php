<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Contracts;

use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;

interface PostContentHandlerInterface
{
    public function supports(PostContentTypeEnum $contentType): bool;

    public function attach(Post $post, PostContentAttachDto $dto): void;

    /**
     * @return list<string>
     */
    public function eagerLoadRelations(): array;
}
