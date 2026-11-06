<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Data\DTO\CreateNoteDto;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\CreateNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\CreateRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class CreateNoteAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::NOTE;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::CREATED;

    public function __construct(
        private readonly CreateNoteTask $createNoteTask,
    ) {
        parent::__construct();
    }

    public function handle(CreateNoteDto $dto): Note
    {
        $note = $this->createNoteTask->run($dto);

        $this->recordUseCase($note);

        return $note;
    }

    public function asController(CreateRequest $request): JsonResponse
    {
        $dto = CreateNoteDto::from($request->validated());
        $dto->user_id = (int) auth()->id();

        $note = $this->handle($dto);

        return fractal($note, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->addMeta(['message' => 'Note successfully created!'])
            ->respond(201, [], JSON_PRETTY_PRINT);
    }
}
