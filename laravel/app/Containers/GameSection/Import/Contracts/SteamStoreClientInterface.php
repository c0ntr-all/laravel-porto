<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Contracts;

use App\Containers\GameSection\Import\Data\DTO\SteamStorePageDto;

interface SteamStoreClientInterface
{
    public function fetchAppPage(int $steamId): SteamStorePageDto;
}
