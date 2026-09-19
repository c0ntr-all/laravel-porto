<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Contracts;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;

interface KinopoiskMovieApiClientInterface
{
    public function fetch(int $kpId): KinopoiskApiResponseDto;
}
