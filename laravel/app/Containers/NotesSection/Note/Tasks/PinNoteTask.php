<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\PinOrder;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class PinNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    public function run(Note $note): Note
    {
        if ($note->pin_position !== null) {
            return $this->present($note);
        }

        return DB::transaction(function () use ($note): Note {
            $pinned = $this->noteRepository->pinnedForUpdate((int) $note->user_id);
            $max = $pinned->max('pin_position');
            $position = PinOrder::between($max === null ? null : (int) $max, null);

            if ($position === null) {
                $ordered = $pinned->sortBy(fn (Note $item) => [(int) $item->pin_position, (int) $item->id])->values();
                $positions = [];
                foreach ($ordered as $index => $item) {
                    $positions[(int) $item->id] = ($index + 1) * PinOrder::GAP;
                }
                $this->noteRepository->setPinPositions($positions);
                $position = (count($positions) + 1) * PinOrder::GAP;
            }

            $this->noteRepository->setPinPosition($note, $position);

            return $this->present($note);
        });
    }

    private function present(Note $note): Note
    {
        return $this->noteRepository->loadForResponse($note->refresh());
    }
}
