<?php

declare(strict_types=1);

namespace App\Containers\GameSection\SteamUser\UI\CLI\Commands;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Containers\GameSection\SteamUser\Exceptions\SteamIdAlreadyLinkedException;
use App\Containers\GameSection\SteamUser\Tasks\ImportSteamUserForUserTask;
use App\Ship\Parents\Commands\ConsoleCommand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImportSteamUserCommand extends ConsoleCommand
{
    protected $signature = 'steam:import-user
                            {user : Local user id to attach the Steam profile to}
                            {steam_id : Steam64 ID (e.g. 76561197997038451)}';

    protected $description = 'Import a Steam player summary and link it to a local user';

    public function handle(ImportSteamUserForUserTask $importSteamUserForUserTask): int
    {
        $userId = (int) $this->argument('user');
        $steamId = trim((string) $this->argument('steam_id'));

        if ($userId < 1) {
            $this->error('User id must be a positive integer.');

            return self::FAILURE;
        }

        if ($steamId === '' || ! ctype_digit($steamId)) {
            $this->error('Steam ID must be a numeric Steam64 identifier.');

            return self::FAILURE;
        }

        try {
            $user = User::query()->findOrFail($userId);
            $steamUser = $importSteamUserForUserTask->run($user, $steamId);
        } catch (ModelNotFoundException) {
            $this->error('Local user '.$userId.' was not found.');

            return self::FAILURE;
        } catch (SteamIdAlreadyLinkedException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (SteamWebApiException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Steam profile linked to user '.$userId.'.');
        $this->table(
            ['Field', 'Value'],
            [
                ['user_id', (string) $steamUser->user_id],
                ['steam_id', $steamUser->steam_id],
                ['persona_name', $steamUser->persona_name],
                ['real_name', (string) $steamUser->real_name],
                ['profile_url', (string) $steamUser->profile_url],
                ['loc_country_code', (string) $steamUser->loc_country_code],
            ],
        );

        return self::SUCCESS;
    }
}
