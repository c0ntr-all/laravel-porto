<?php

declare(strict_types=1);

use App\Containers\GameSection\Achievement\UI\Actions\GetAchievementAction;
use Illuminate\Support\Facades\Route;

Route::get('games/{game}/achievements/{achievement}', GetAchievementAction::class)
    ->middleware(['auth:api'])
    ->scopeBindings()
    ->whereNumber(['game', 'achievement']);
