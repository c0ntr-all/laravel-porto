<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Exceptions\InvalidNoteMoveException;
use App\Containers\NotesSection\Note\Exceptions\NoteNotPinnedException;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\PinOrder;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class MovePinnedNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    public function run(Note $note, ?int $beforeId): Note
    {
        if ($note->pin_position === null) {
            throw new NoteNotPinnedException;
        }

        if ($beforeId !== null && $beforeId === (int) $note->id) {
            throw new InvalidNoteMoveException('Нельзя поставить заметку перед самой собой.');
        }

        return DB::transaction(function () use ($note, $beforeId): Note {
            $pinned = $this->noteRepository
                ->pinnedForUpdate((int) $note->user_id)
                ->sort(function (Note $left, Note $right): int {
                    $byPosition = ((int) $left->pin_position) <=> ((int) $right->pin_position);

                    return $byPosition !== 0 ? $byPosition : ((int) $left->id) <=> ((int) $right->id);
                })
                ->values();

            $currentIds = $pinned->map(fn (Note $item): int => (int) $item->id)->all();
            if (! in_array((int) $note->id, $currentIds, true)) {
                throw new NoteNotPinnedException;
            }

            $orderedIds = array_values(array_filter(
                $currentIds,
                fn (int $id): bool => $id !== (int) $note->id,
            ));

            if ($beforeId === null) {
                $orderedIds[] = (int) $note->id;
            } else {
                $index = array_search($beforeId, $orderedIds, true);
                if ($index === false) {
                    throw new InvalidNoteMoveException('Закреп, перед которым нужно встать, не найден.');
                }

                array_splice($orderedIds, $index, 0, [(int) $note->id]);
            }

            if ($currentIds === $orderedIds) {
                return $this->noteRepository->loadForResponse($note->refresh());
            }

            $byId = $pinned->keyBy(fn (Note $item): int => (int) $item->id);
            $newIndex = (int) array_search((int) $note->id, $orderedIds, true);
            $prev = $newIndex > 0 ? $byId->get($orderedIds[$newIndex - 1]) : null;
            $next = $newIndex < count($orderedIds) - 1 ? $byId->get($orderedIds[$newIndex + 1]) : null;

            $position = PinOrder::between(
                $prev instanceof Note ? (int) $prev->pin_position : null,
                $next instanceof Note ? (int) $next->pin_position : null,
            );

            if ($position !== null) {
                $this->noteRepository->setPinPosition($note, $position);

                return $this->noteRepository->loadForResponse($note->refresh());
            }

            $positions = [];
            foreach ($orderedIds as $index => $id) {
                $positions[$id] = ($index + 1) * PinOrder::GAP;
            }
            $this->noteRepository->setPinPositions($positions);

            return $this->noteRepository->loadForResponse($note->refresh());
        });
    }
}
