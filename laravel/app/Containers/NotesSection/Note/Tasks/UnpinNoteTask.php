<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UnpinNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    public function run(Note $note): Note
    {
        if ($note->pin_position === null) {
            return $this->noteRepository->loadForResponse($note);
        }

        $this->noteRepository->setPinPosition($note, null);

        return $this->noteRepository->loadForResponse($note->refresh());
    }
}
