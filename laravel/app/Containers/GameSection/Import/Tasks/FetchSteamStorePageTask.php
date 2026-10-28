<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Tasks;

use App\Containers\GameSection\Import\Contracts\SteamStoreClientInterface;
use App\Containers\GameSection\Import\Data\DTO\SteamStorePageDto;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FetchSteamStorePageTask extends ParentTask
{
    public function __construct(
        private readonly SteamStoreClientInterface $steamStoreClient,
    ) {}

    public function run(int $steamId): SteamStorePageDto
    {
        return $this->steamStoreClient->fetchAppPage($steamId);
    }
}
