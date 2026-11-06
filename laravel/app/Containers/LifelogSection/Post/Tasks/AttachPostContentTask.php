<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Tasks;

use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Handlers\AlbumPostContentHandler;
use App\Containers\LifelogSection\Post\Handlers\MoviePostContentHandler;
use App\Containers\LifelogSection\Post\Handlers\NotePostContentHandler;
use App\Containers\LifelogSection\Post\Handlers\TaskPostContentHandler;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Ship\Parents\Tasks\Task as ParentTask;

class AttachPostContentTask extends ParentTask
{
    public function __construct(
        private readonly MoviePostContentHandler $moviePostContentHandler,
        private readonly TaskPostContentHandler $taskPostContentHandler,
        private readonly AlbumPostContentHandler $albumPostContentHandler,
        private readonly NotePostContentHandler $notePostContentHandler,
    ) {
    }

    public function run(Post $post, PostContentAttachDto $dto): void
    {
        $this->syncMovie($post, $dto);
        $this->syncTask($post, $dto);
        $this->syncAlbum($post, $dto);
        $this->syncNote($post, $dto);
    }

    private function syncMovie(Post $post, PostContentAttachDto $dto): void
    {
        if (
            $dto->content_type === PostContentTypeEnum::MOVIE
            || $dto->hasMoviePayload()
            || $dto->hasWatchPayload()
        ) {
            $this->moviePostContentHandler->attach($post, $dto);

            return;
        }

        if ($dto->has_content_type_input) {
            $post->movies()->detach();
        }
    }

    private function syncTask(Post $post, PostContentAttachDto $dto): void
    {
        if (!$dto->has_task_input) {
            return;
        }

        if ($dto->task_id === null) {
            $post->tasks()->detach();

            return;
        }

        $this->taskPostContentHandler->attach($post, $dto);
    }

    private function syncAlbum(Post $post, PostContentAttachDto $dto): void
    {
        if (!$dto->has_album_input) {
            return;
        }

        if ($dto->album_id === null) {
            $post->albums()->detach();

            return;
        }

        $this->albumPostContentHandler->attach($post, $dto);
    }

    private function syncNote(Post $post, PostContentAttachDto $dto): void
    {
        if (!$dto->has_note_input) {
            return;
        }

        if ($dto->note_id === null) {
            $post->notes()->detach();

            return;
        }

        $this->notePostContentHandler->attach($post, $dto);
    }
}
