<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\UI\Actions;

use App\Containers\NotesSection\Note\Data\DTO\ListNotesDto;
use App\Containers\NotesSection\Note\Tasks\ListNotesTask;
use App\Containers\NotesSection\Note\UI\API\Requests\ListRequest;
use App\Containers\NotesSection\Note\UI\API\Transformers\NoteTransformer;
use App\Ship\Enums\ContainerAliasEnum;
use App\Ship\Parents\Actions\BaseAction;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\CursorPaginator;
use League\Fractal\Pagination\Cursor;

class ListNotesAction extends BaseAction
{
    public function __construct(
        private readonly ListNotesTask $listNotesTask,
    ) {}

    public function handle(ListNotesDto $dto): CursorPaginator
    {
        return $this->listNotesTask->run($dto);
    }

    public function asController(ListRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $dto = ListNotesDto::from([
            'user_id' => (int) auth()->id(),
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ]);

        $notes = $this->handle($dto);

        return fractal($notes, new NoteTransformer)
            ->withResourceName(ContainerAliasEnum::NOTE->value)
            ->withCursor($this->cursorFromPaginator($notes))
            ->addMeta($this->cursorMeta($notes))
            ->respond(200, [], JSON_PRETTY_PRINT);
    }

    private function cursorFromPaginator(CursorPaginator $paginator): Cursor
    {
        return new Cursor(
            $paginator->cursor()?->encode(),
            $paginator->previousCursor()?->encode(),
            $paginator->nextCursor()?->encode(),
            $paginator->count(),
        );
    }

    /**
     * @return array{
     *     per_page: int,
     *     has_more: bool,
     *     next_cursor: string|null,
     *     prev_cursor: string|null,
     *     next_page_url: string|null,
     *     prev_page_url: string|null
     * }
     */
    private function cursorMeta(CursorPaginator $paginator): array
    {
        return [
            'per_page' => $paginator->perPage(),
            'has_more' => $paginator->hasMorePages(),
            'next_cursor' => $paginator->nextCursor()?->encode(),
            'prev_cursor' => $paginator->previousCursor()?->encode(),
            'next_page_url' => $paginator->nextPageUrl(),
            'prev_page_url' => $paginator->previousPageUrl(),
        ];
    }
}
