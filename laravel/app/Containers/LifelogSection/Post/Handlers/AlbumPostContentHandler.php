<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Handlers;

use App\Containers\GallerySection\Album\Models\Album;
use App\Containers\GallerySection\Album\Tasks\FindAlbumByIdForUserTask;
use App\Containers\LifelogSection\Post\Contracts\PostContentHandlerInterface;
use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Enums\ContainerAliasEnum;
use InvalidArgumentException;

class AlbumPostContentHandler implements PostContentHandlerInterface
{
    public function __construct(
        private readonly FindAlbumByIdForUserTask $findAlbumByIdForUserTask,
    ) {
    }

    public function supports(PostContentTypeEnum $contentType): bool
    {
        return false;
    }

    public function attach(Post $post, PostContentAttachDto $dto): void
    {
        $album = $this->resolveAlbum($post, $dto);
        $post->attachAlbum($album);
    }

    public function subjectableType(): ContainerAliasEnum
    {
        return ContainerAliasEnum::GALLERY_ALBUM;
    }

    public function eagerLoadRelations(): array
    {
        return ['albums'];
    }

    private function resolveAlbum(Post $post, PostContentAttachDto $dto): Album
    {
        if ($dto->album_id !== null) {
            return $this->findAlbumByIdForUserTask->run($dto->album_id, (int) $post->user_id);
        }

        $existing = $this->currentAlbum($post);

        if ($existing) {
            return $existing;
        }

        throw new InvalidArgumentException('Album post requires album_id.');
    }

    private function currentAlbum(Post $post): ?Album
    {
        return $post->relationLoaded('albums')
            ? $post->albums->first()
            : $post->albums()->first();
    }
}
