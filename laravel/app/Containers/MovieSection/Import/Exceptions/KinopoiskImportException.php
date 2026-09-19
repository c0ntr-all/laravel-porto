<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Exceptions;

use App\Containers\MovieSection\Import\Models\MovieImport;
use RuntimeException;
use Throwable;

class KinopoiskImportException extends RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus = 422,
        public readonly ?MovieImport $import = null,
        ?Throwable $previous = null,
        public readonly array $context = [],
    ) {
        parent::__construct($message, 0, $previous);
    }

    public function withImport(MovieImport $import): KinopoiskImportException
    {
        return new KinopoiskImportException(
            $this->getMessage(),
            $this->httpStatus,
            $import,
            $this,
            $this->context,
        );
    }
}
