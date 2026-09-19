<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Exceptions;

class KinopoiskParseException extends KinopoiskImportException
{
    public function __construct(string $message, array $context = [])
    {
        parent::__construct($message, 422, null, null, $context);
    }
}
