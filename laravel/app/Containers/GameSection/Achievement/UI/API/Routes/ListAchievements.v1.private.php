<?php

declare(strict_types=1);

use App\Containers\GameSection\Achievement\UI\Actions\ListAchievementsAction;
use Illuminate\Support\Facades\Route;

Route::get('games/{game}/achievements', ListAchievementsAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
