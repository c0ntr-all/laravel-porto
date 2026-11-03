<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\DTO\UpdateNoteDto;
use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Support\WysiwygHtmlSanitizer;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Support\Facades\DB;
use Spatie\LaravelData\Optional;

class UpdateNoteTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
        private readonly WysiwygHtmlSanitizer $htmlSanitizer,
        private readonly AssertNoteParentTask $assertNoteParentTask,
        private readonly SyncNoteLinksTask $syncNoteLinksTask,
    ) {}

    public function run(Note $note, UpdateNoteDto $dto): Note
    {
        $attributes = [];

        if (! ($dto->name instanceof Optional)) {
            $attributes['name'] = trim($dto->name);
        }

        if (! ($dto->content instanceof Optional)) {
            $attributes['content'] = $this->htmlSanitizer->sanitize($dto->content);
        }

        if (! ($dto->parent_id instanceof Optional)) {
            $this->assertNoteParentTask->run($note, $dto->parent_id);
            $attributes['parent_id'] = $dto->parent_id;
        }

        return DB::transaction(function () use ($note, $attributes, $dto): Note {
            $note = $this->noteRepository->updateIfUnchanged($note, $attributes, $dto->updated_at);
            $resolution = $this->syncNoteLinksTask->run($note);
            $note = $this->noteRepository->loadForResponse($note);
            $note->ambiguousLinks = $resolution['ambiguous'];

            return $note;
        });
    }
}
