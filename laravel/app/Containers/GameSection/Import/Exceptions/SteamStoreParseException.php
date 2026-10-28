<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Exceptions;

use Throwable;

class SteamStoreParseException extends SteamStoreException
{
    public function __construct(string $message, ?Throwable $previous = null)
    {
        parent::__construct($message, 422, null, $previous);
    }
}
