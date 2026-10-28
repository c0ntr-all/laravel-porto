<?php

declare(strict_types=1);

use App\Containers\GameSection\Achievement\UI\Actions\UpdateAchievementAction;
use Illuminate\Support\Facades\Route;

Route::patch('games/{game}/achievements/{achievement}', UpdateAchievementAction::class)
    ->middleware(['auth:api'])
    ->scopeBindings()
    ->whereNumber(['game', 'achievement']);
