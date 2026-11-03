<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Exceptions;

use Illuminate\Http\JsonResponse;
use Symfony\Component\HttpKernel\Exception\HttpException;

class NoteConflictException extends HttpException
{
    public function __construct(public readonly string $currentUpdatedAt)
    {
        parent::__construct(409, 'Заметку уже изменили в другой вкладке. Обновите её и повторите сохранение.');
    }

    public function render(): JsonResponse
    {
        return response()->json([
            'message' => $this->getMessage(),
            'updated_at' => $this->currentUpdatedAt,
        ], 409);
    }
}
