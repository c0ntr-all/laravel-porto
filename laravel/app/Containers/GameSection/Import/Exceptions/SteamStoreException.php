<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Exceptions;

use App\Containers\GameSection\Import\Models\GameImport;
use RuntimeException;
use Throwable;

class SteamStoreException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 422,
        public readonly ?GameImport $import = null,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function withImport(GameImport $import): self
    {
        return new self(
            $this->getMessage(),
            $this->httpStatus,
            $import,
            $this,
        );
    }
}
