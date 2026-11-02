<?php

declare(strict_types=1);

use App\Containers\GameSection\PlayerAchievement\UI\Actions\ImportPlayerAchievementsFromSteamAction;
use Illuminate\Support\Facades\Route;

Route::post('games/{game}/player-achievements/import', ImportPlayerAchievementsFromSteamAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
