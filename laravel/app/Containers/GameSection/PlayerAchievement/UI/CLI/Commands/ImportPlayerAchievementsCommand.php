<?php

declare(strict_types=1);

namespace App\Containers\GameSection\PlayerAchievement\UI\CLI\Commands;

use App\Containers\AppSection\User\Models\User;
use App\Containers\GameSection\Achievement\Exceptions\GameMissingSteamIdException;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\PlayerAchievement\Exceptions\SteamUserNotLinkedException;
use App\Containers\GameSection\PlayerAchievement\Tasks\UpsertPlayerAchievementsFromSteamTask;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Ship\Parents\Commands\ConsoleCommand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImportPlayerAchievementsCommand extends ConsoleCommand
{
    protected $signature = 'game:import-player-achievements
                            {user : Local user id with linked Steam profile}
                            {game : Local game id}
                            {--language= : Steam Web API language override}';

    protected $description = 'Import Steam player achievements for a local user and game';

    public function handle(UpsertPlayerAchievementsFromSteamTask $upsertPlayerAchievementsFromSteamTask): int
    {
        $userId = (int) $this->argument('user');
        $gameId = (int) $this->argument('game');
        $languageOption = $this->option('language');
        $language = is_string($languageOption) && $languageOption !== '' ? $languageOption : null;

        try {
            $user = User::query()->findOrFail($userId);
            $game = Game::query()->findOrFail($gameId);
            $result = $upsertPlayerAchievementsFromSteamTask->run($user, $game, $language);
        } catch (ModelNotFoundException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        } catch (GameMissingSteamIdException|SteamUserNotLinkedException|SteamWebApiException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Imported player achievements for user '.$userId.' and game '.$gameId.'.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['created', (string) $result['created']],
                ['updated', (string) $result['updated']],
                ['skipped', (string) $result['skipped']],
                ['total', (string) $result['total']],
                ['catalog_imported', $result['catalog_imported'] ? 'yes' : 'no'],
                ['game_name', (string) $result['game_name']],
            ],
        );

        return self::SUCCESS;
    }
}
