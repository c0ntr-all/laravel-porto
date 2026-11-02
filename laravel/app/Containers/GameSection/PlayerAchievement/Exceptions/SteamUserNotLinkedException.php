<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\Exceptions;

use RuntimeException;

class SteamUserNotLinkedException extends RuntimeException
{
    public function __construct(int $userId)
    {
        parent::__construct('User '.$userId.' has no linked Steam profile.');
    }
}
