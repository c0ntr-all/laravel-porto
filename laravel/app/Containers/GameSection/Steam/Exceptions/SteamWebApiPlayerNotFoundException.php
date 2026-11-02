<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Exceptions;

class SteamWebApiPlayerNotFoundException extends SteamWebApiException
{
    public function __construct(string $steamId)
    {
        parent::__construct('Steam player '.$steamId.' was not found.', 404);
    }
}
