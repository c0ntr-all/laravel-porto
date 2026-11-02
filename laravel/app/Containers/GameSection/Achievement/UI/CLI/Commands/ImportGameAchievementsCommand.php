<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Achievement\UI\CLI\Commands;

use App\Containers\GameSection\Achievement\Exceptions\GameMissingSteamIdException;
use App\Containers\GameSection\Achievement\Tasks\UpsertGameAchievementsFromSteamTask;
use App\Containers\GameSection\Game\Models\Game;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Ship\Parents\Commands\ConsoleCommand;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ImportGameAchievementsCommand extends ConsoleCommand
{
    protected $signature = 'game:import-achievements
                            {game : Local game id}
                            {--language= : Steam Web API language override}';

    protected $description = 'Import Steam achievement schema for a local game';

    public function handle(UpsertGameAchievementsFromSteamTask $upsertGameAchievementsFromSteamTask): int
    {
        $gameId = (int) $this->argument('game');
        $languageOption = $this->option('language');
        $language = is_string($languageOption) && $languageOption !== '' ? $languageOption : null;

        try {
            $game = Game::query()->findOrFail($gameId);
            $result = $upsertGameAchievementsFromSteamTask->run($game, $language);
        } catch (ModelNotFoundException) {
            $this->error('Game '.$gameId.' was not found.');

            return self::FAILURE;
        } catch (GameMissingSteamIdException|SteamWebApiException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }

        $this->info('Imported achievements for game '.$gameId.'.');
        $this->table(
            ['Metric', 'Value'],
            [
                ['created', (string) $result['created']],
                ['updated', (string) $result['updated']],
                ['total', (string) $result['total']],
            ],
        );

        return self::SUCCESS;
    }
}
