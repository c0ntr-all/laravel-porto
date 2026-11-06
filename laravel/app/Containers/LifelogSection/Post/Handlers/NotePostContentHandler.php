<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Handlers;

use App\Containers\LifelogSection\Post\Contracts\PostContentHandlerInterface;
use App\Containers\LifelogSection\Post\Data\DTO\PostContentAttachDto;
use App\Containers\LifelogSection\Post\Enums\PostContentTypeEnum;
use App\Containers\LifelogSection\Post\Models\Post;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\FindNoteByIdForUserTask;
use App\Ship\Enums\ContainerAliasEnum;
use InvalidArgumentException;

class NotePostContentHandler implements PostContentHandlerInterface
{
    public function __construct(
        private readonly FindNoteByIdForUserTask $findNoteByIdForUserTask,
    ) {
    }

    public function supports(PostContentTypeEnum $contentType): bool
    {
        return false;
    }

    public function attach(Post $post, PostContentAttachDto $dto): void
    {
        $note = $this->resolveNote($post, $dto);
        $post->attachNote($note);
    }

    public function subjectableType(): ContainerAliasEnum
    {
        return ContainerAliasEnum::NOTE;
    }

    public function eagerLoadRelations(): array
    {
        return ['notes'];
    }

    private function resolveNote(Post $post, PostContentAttachDto $dto): Note
    {
        if ($dto->note_id !== null) {
            return $this->findNoteByIdForUserTask->run($dto->note_id, (int) $post->user_id);
        }

        $existing = $this->currentNote($post);

        if ($existing) {
            return $existing;
        }

        throw new InvalidArgumentException('Note post requires note_id.');
    }

    private function currentNote(Post $post): ?Note
    {
        return $post->relationLoaded('notes')
            ? $post->notes->first()
            : $post->notes()->first();
    }
}
