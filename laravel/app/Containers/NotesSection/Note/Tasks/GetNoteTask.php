<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Tasks\Task as ParentTask;

class GetNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly ResolveNoteLinksTask $resolveNoteLinksTask,
    ) {}

    public function run(Note $note): Note
    {
        $note = $this->noteRepository->loadForResponse($note);
        $note->ambiguousLinks = $this->resolveNoteLinksTask->run($note)['ambiguous'];

        return $note;
    }
}
