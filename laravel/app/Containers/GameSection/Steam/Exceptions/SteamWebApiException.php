<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Exceptions;

use RuntimeException;
use Throwable;

class SteamWebApiException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 422,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }
}
