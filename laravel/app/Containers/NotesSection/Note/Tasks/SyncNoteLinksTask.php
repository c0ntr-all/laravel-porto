<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\Repositories\NoteLinkRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Ship\Parents\Tasks\Task as ParentTask;

class SyncNoteLinksTask extends ParentTask
{
    public function __construct(
        private readonly ResolveNoteLinksTask $resolveNoteLinksTask,
        private readonly NoteLinkRepository $noteLinkRepository,
    ) {}

    /**
     * @return array{
     *     links: list<array{id: int, name: string, label: string}>,
     *     unresolved: list<string>,
     *     ambiguous: list<string>
     * }
     */
    public function run(Note $note): array
    {
        $resolution = $this->resolveNoteLinksTask->run($note);
        $this->noteLinkRepository->replaceOutgoing($note, $resolution['links']);
        $note->unsetRelation('outgoingLinks');

        return $resolution;
    }
}
