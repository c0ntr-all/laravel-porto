<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Exceptions\InvalidNoteParentException;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Tasks\Task as ParentTask;

class AssertNoteParentTask extends ParentTask
{
    public function run(Note $note, ?int $parentId): void
    {
        if ($parentId === null) {
            return;
        }

        if ($note->exists && $parentId === (int) $note->id) {
            throw new InvalidNoteParentException('Заметка не может быть родителем самой себя.');
        }

        $cursor = Note::query()->forUser((int) $note->user_id)->find($parentId);
        if (! $cursor instanceof Note) {
            throw new InvalidNoteParentException('Родительская заметка не найдена.');
        }

        $seen = [];
        $guard = 0;
        while ($guard < 100) {
            if (isset($seen[$cursor->id])) {
                throw new InvalidNoteParentException('В родителях заметки уже есть цикл.');
            }

            $seen[$cursor->id] = true;

            if ($note->exists && (int) $cursor->id === (int) $note->id) {
                throw new InvalidNoteParentException('Нельзя вложить заметку в собственного потомка.');
            }

            if ($cursor->parent_id === null) {
                return;
            }

            $next = Note::query()->forUser((int) $note->user_id)->find($cursor->parent_id);
            if (! $next instanceof Note) {
                return;
            }

            $cursor = $next;
            $guard++;
        }

        throw new InvalidNoteParentException('Слишком глубокая вложенность.');
    }
}
