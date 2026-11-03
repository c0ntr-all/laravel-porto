<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\GetNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\GetRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class GetNoteAction extends BaseAction
{
    public function __construct(
        private readonly GetNoteTask $getNoteTask,
    ) {}

    public function handle(Note $note): Note
    {
        return $this->getNoteTask->run($note);
    }

    public function asController(Note $note, GetRequest $request): JsonResponse
    {
        $note = $this->handle($note);

        return fractal($note, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
