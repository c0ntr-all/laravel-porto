<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Contracts;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;

interface KinopoiskFilmPageClientInterface
{
    public function fetch(int $kpId): KinopoiskPageDto;
}
