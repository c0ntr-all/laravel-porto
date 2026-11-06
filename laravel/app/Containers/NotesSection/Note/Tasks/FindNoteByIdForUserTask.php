<?php declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class FindNoteByIdForUserTask extends ParentTask
{
    public function run(int $noteId, int $userId): Note
    {
        $note = Note::query()
            ->where('user_id', $userId)
            ->find($noteId);

        if (!$note) {
            throw (new ModelNotFoundException())->setModel(Note::class, [$noteId]);
        }

        return $note;
    }
}
