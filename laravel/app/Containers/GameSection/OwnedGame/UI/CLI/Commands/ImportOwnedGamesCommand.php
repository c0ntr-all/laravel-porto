<?php

declare(strict_types=1);

namespace App\Containers\GameSection\OwnedGame\UI\CLI\Commands;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\OwnedGame\Exceptions\SteamUserNotLinkedException;
use App\Containers\GameSection\OwnedGame\Tasks\UpsertOwnedGamesFromSteamTask;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Ship\Parents\Commands\ConsoleCommand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImportOwnedGamesCommand extends ConsoleCommand
{
    protected $signature = 'game:import-owned-games
                            {user : Local user id with linked Steam profile}';

    protected $description = 'Import Steam owned games playtime for a local user';

    public function handle(UpsertOwnedGamesFromSteamTask $upsertOwnedGamesFromSteamTask): int
    {
        $userId = (int) $this->argument('user');

        try {
            $user = User::query()->findOrFail($userId);
            $result = $upsertOwnedGamesFromSteamTask->run($user);
        } catch (ModelNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (SteamUserNotLinkedException|SteamWebApiException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Imported owned games for user '.$userId.'.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['created', (string) $result['created']],
                ['updated', (string) $result['updated']],
                ['skipped', (string) $result['skipped']],
                ['total', (string) $result['total']],
                ['game_count', (string) $result['game_count']],
            ],
        );

        return self::SUCCESS;
    }
}
