<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\PinNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\PinRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;

class PinNoteAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::NOTE;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::UPDATED;

    public function __construct(
        private readonly PinNoteTask $pinNoteTask,
    ) {
        parent::__construct();
    }

    public function asController(Note $note, PinRequest $request): JsonResponse
    {
        $previousPin = $note->pin_position;
        $note = $this->pinNoteTask->run($note);

        if ($this->pinPositionChanged($previousPin, $note->pin_position)) {
            $this->recordUseCase($note);
        }

        return fractal($note, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->addMeta(['message' => 'Note pinned!'])
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function pinPositionChanged(mixed $before, mixed $after): bool
    {
        if ($before === null || $after === null) {
            return $before !== $after;
        }

        return (int) $before !== (int) $after;
    }
}
