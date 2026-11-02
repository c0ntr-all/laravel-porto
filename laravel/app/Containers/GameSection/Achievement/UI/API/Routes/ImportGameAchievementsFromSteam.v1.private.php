<?php

declare(strict_types=1);

use App\Containers\GameSection\Achievement\UI\Actions\ImportGameAchievementsFromSteamAction;
use Illuminate\Support\Facades\Route;

Route::post('games/{game}/achievements/import', ImportGameAchievementsFromSteamAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
