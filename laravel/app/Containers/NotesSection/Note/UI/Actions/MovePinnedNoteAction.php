<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Data\DTO\MovePinnedNoteDto;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\MovePinnedNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\MovePinnedNoteRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class MovePinnedNoteAction extends BaseAction
{
    public function __construct(
        private readonly MovePinnedNoteTask $movePinnedNoteTask,
    ) {}

    public function asController(Note $note, MovePinnedNoteRequest $request): JsonResponse
    {
        $dto = MovePinnedNoteDto::from($request->validated());
        $note = $this->movePinnedNoteTask->run($note, $dto->before_id);

        return fractal($note, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->addMeta(['message' => 'Pinned note moved!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
