<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Data\Repositories;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Models\NoteLink;

class NoteLinkRepository
{
    /**
     * @param  list<array{id: int, name: string, label: string}>  $links
     */
    public function replaceOutgoing(Note $note, array $links): void
    {
        NoteLink::query()->where('source_note_id', $note->id)->delete();

        foreach ($links as $link) {
            NoteLink::query()->create([
                'user_id' => $note->user_id,
                'source_note_id' => $note->id,
                'target_note_id' => $link['id'],
                'label' => $link['label'],
            ]);
        }
    }

    public function deleteForNote(Note $note): void
    {
        NoteLink::query()
            ->where(function ($query) use ($note): void {
                $query->where('source_note_id', $note->id)
                    ->orWhere('target_note_id', $note->id);
            })
            ->delete();
    }
}
