<?php

declare(strict_types=1);

namespace App\Containers\AppSection\SteamUser\Data\Repositories;

use App\Containers\AppSection\SteamUser\Data\DTO\SteamUserUpsertData;
use App\Containers\AppSection\SteamUser\Models\SteamUser;
use App\Containers\AppSection\User\Models\User;

class SteamUserRepository
{
    public function findByUser(User $user): ?SteamUser
    {
        return SteamUser::query()->where('user_id', $user->id)->first();
    }

    public function findBySteamId(string $steamId): ?SteamUser
    {
        return SteamUser::query()->where('steam_id', $steamId)->first();
    }

    public function upsert(User $user, SteamUserUpsertData $dto): SteamUser
    {
        return SteamUser::query()->updateOrCreate(
            ['user_id' => $user->id],
            $dto->toArray(),
        );
    }
}
