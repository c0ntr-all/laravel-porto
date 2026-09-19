<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Exceptions;

class KinopoiskFilmNotFoundException extends KinopoiskImportException
{
    public function __construct(int $kpId, array $context = [])
    {
        parent::__construct("Film {$kpId} was not found on Kinopoisk.", 404, null, null, $context);
    }
}
