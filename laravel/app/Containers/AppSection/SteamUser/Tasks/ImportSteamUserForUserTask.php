<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\Tasks;

use App\Containers\AppSection\Steam\Tasks\FetchSteamPlayerSummaryTask;
use App\Containers\AppSection\SteamUser\Data\Repositories\SteamUserRepository;
use App\Containers\AppSection\SteamUser\Exceptions\SteamIdAlreadyLinkedException;
use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\User\Models\User;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ImportSteamUserForUserTask extends ParentTask
{
    public function __construct(
        private readonly FetchSteamPlayerSummaryTask $fetchSteamPlayerSummaryTask,
        private readonly UpsertSteamUserFromApiTask $upsertSteamUserFromApiTask,
        private readonly SteamUserRepository $steamUserRepository,
    ) {}

    public function run(User $user, string $steamId): SteamUser
    {
        $steamId = trim($steamId);
        $existing = $this->steamUserRepository->findBySteamId($steamId);
        if ($existing !== null && (int) $existing->user_id !== (int) $user->id) {
            throw new SteamIdAlreadyLinkedException($steamId, (int) $existing->user_id);
        }

        $summary = $this->fetchSteamPlayerSummaryTask->run($steamId);

        return $this->upsertSteamUserFromApiTask->run($user, $summary);
    }
}
