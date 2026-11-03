<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Models\Note;
use App\Containers\NotesSection\Note\Tasks\DeleteNoteTask;
use App\Containers\NotesSection\Note\UI\API\Requests\DeleteRequest;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;

class DeleteNoteAction extends BaseAction
{
    public function __construct(
        private readonly DeleteNoteTask $deleteNoteTask,
    ) {}

    public function handle(Note $note): bool
    {
        return $this->deleteNoteTask->run($note);
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
