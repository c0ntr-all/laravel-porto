<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\DeleteNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\DeleteRequest;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Enums\EventTypesEnum;
use App\Ship\Parents\Actions\UseCaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class DeleteNoteAction extends UseCaseAction
{
    protected ?ContainerAliasEnum $containerAliasEnum = ContainerAliasEnum::NOTE;
    protected ?EventTypesEnum $eventTypesEnum = EventTypesEnum::DELETED;

    public function __construct(
        private readonly DeleteNoteTask $deleteNoteTask,
    ) {
        parent::__construct();
    }

    public function handle(Note $note): bool
    {
        return DB::transaction(function () use ($note): bool {
            $this->recordUseCase($note);

            return $this->deleteNoteTask->run($note);
        });
    }

    public function asController(Note $note, DeleteRequest $request): JsonResponse
    {
        $this->handle($note);

        return response()->json([
            'meta' => [
                'message' => 'Note successfully deleted!',
            ],
        ]);
    }
}
