<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Data\DTO\UpdateNoteDto;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\UpdateNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\UpdateRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class UpdateNoteAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::NOTE;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly UpdateNoteTask $updateNoteTask,
    ) {
        parent::__construct();
    }

    public function handle(Note $note, UpdateNoteDto $dto): Note
    {
        $note = $this->updateNoteTask->run($note, $dto);

        $this->recordUseCase($note);

        return $note;
    }

    public function asController(Note $note, UpdateRequest $request): JsonResponse
    {
        $dto = UpdateNoteDto::from($request->validated());
        $note = $this->handle($note, $dto);

        return fractal($note, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->addMeta(['message' => 'Note successfully updated!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }
}
