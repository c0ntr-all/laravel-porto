<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\Repositories\NoteLinkRepository;
use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class DeleteNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly NoteLinkRepository $noteLinkRepository,
    ) {}

    public function run(Note $note): bool
    {
        return DB::transaction(function () use ($note): bool {
            $this->noteRepository->promoteChildren($note);
            $this->noteLinkRepository->deleteForNote($note);
            $this->noteRepository->clearPin($note);

            return $this->noteRepository->delete($note);
        });
    }
}
