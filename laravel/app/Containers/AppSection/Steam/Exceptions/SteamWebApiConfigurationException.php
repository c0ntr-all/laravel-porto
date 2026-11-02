<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Exceptions;

class SteamWebApiConfigurationException extends SteamWebApiException
{
    public function __construct(string $message = 'STEAM_WEB_API_KEY is not configured.')
    {
        parent::__construct($message, 503);
    }
}
