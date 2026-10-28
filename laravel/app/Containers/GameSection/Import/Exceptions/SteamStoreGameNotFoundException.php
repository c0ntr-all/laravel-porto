<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Exceptions;

class SteamStoreGameNotFoundException extends SteamStoreException
{
    public function __construct(int $steamId)
    {
        parent::__construct('Steam game '.$steamId.' was not found.', 404);
    }
}
