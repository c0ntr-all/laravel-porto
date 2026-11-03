<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class InvalidNoteMoveException extends HttpException
{
    public function __construct(string $message)
    {
        parent::__construct(422, $message);
    }
}
