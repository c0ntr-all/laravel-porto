<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\Exceptions;

use RuntimeException;

class GameMissingSteamIdException extends RuntimeException
{
    public function __construct(int $gameId)
    {
        parent::__construct('Game '.$gameId.' has no steam_id and cannot import Steam achievements.');
    }
}
