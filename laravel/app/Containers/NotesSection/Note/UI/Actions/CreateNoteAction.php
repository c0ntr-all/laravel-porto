<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Data\DTO\CreateNoteDto;
use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\CreateNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\CreateRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class CreateNoteAction extends BaseAction
{
    public function __construct(
        private readonly CreateNoteTask $createNoteTask,
    ) {}

    public function handle(CreateNoteDto $dto): Note
    {
        return $this->createNoteTask->run($dto);
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
