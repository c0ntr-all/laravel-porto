<?php

declare(strict_types=1);

namespace App\Containers\GameSection\SteamUser\Exceptions;

use RuntimeException;

class SteamIdAlreadyLinkedException extends RuntimeException
{
    public function __construct(string $steamId, int $userId)
    {
        parent::__construct(
            'Steam ID '.$steamId.' is already linked to user '.$userId.'.',
        );
    }
}
