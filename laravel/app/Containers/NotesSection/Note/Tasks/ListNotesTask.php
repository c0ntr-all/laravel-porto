<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Tasks;

use App\Containers\NotesSection\Note\Data\DTO\ListNotesDto;
use App\Containers\NotesSection\Note\Data\Repositories\NoteRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListNotesTask extends ParentTask
{
    public function __construct(
        private readonly NoteRepository $noteRepository,
    ) {}

    public function run(ListNotesDto $dto): CursorPaginator
    {
        return $this->noteRepository->list($dto->user_id, $dto->per_page);
    }
}
