<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\DTO\CreateNoteDto;
use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\WysiwygHtmlSanitizer;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;

class CreateNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly WysiwygHtmlSanitizer $htmlSanitizer,
        private readonly AssertNoteParentTask $assertNoteParentTask,
        private readonly SyncNoteLinksTask $syncNoteLinksTask,
    ) {}

    public function run(CreateNoteDto $dto): Note
    {
        $dto->name = trim($dto->name);
        $dto->content = $this->htmlSanitizer->sanitize($dto->content);

        $pending = new Note([
            'user_id' => $dto->user_id,
        ]);
        $this->assertNoteParentTask->run($pending, $dto->parent_id);

        return DB::transaction(function () use ($dto): Note {
            $note = $this->noteRepository->create($dto);
            $resolution = $this->syncNoteLinksTask->run($note);
            $note = $this->noteRepository->loadForResponse($note);
            $note->ambiguousLinks = $resolution['ambiguous'];

            return $note;
        });
    }
}
