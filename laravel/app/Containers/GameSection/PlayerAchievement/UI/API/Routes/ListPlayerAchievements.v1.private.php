<?php

declare(strict_types=1);

use App\Containers\GameSection\PlayerAchievement\UI\Actions\ListPlayerAchievementsAction;
use Illuminate\Support\Facades\Route;

Route::get('games/{game}/player-achievements', ListPlayerAchievementsAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
