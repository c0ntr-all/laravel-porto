<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\UnpinNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\PinRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class UnpinNoteAction extends BaseAction
{
    public function __construct(
        private readonly UnpinNoteTask $unpinNoteTask,
    ) {}

    public function asController(Note $note, PinRequest $request): JsonResponse
    {
        $note = $this->unpinNoteTask->run($note);

        return fractal($note, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->addMeta(['message' => 'Note unpinned!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
