<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class NoteNotPinnedException extends HttpException
{
    public function __construct()
    {
        parent::__construct(422, 'Перемещать можно только закреплённые заметки.');
    }
}
