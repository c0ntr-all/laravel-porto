<?php

declare(strict_types=1);

use App\Containers\GameSection\Achievement\UI\Actions\DeleteAchievementAction;
use Illuminate\Support\Facades\Route;

Route::delete('games/{game}/achievements/{achievement}', DeleteAchievementAction::class)
    ->middleware(['auth:api'])
    ->scopeBindings()
    ->whereNumber(['game', 'achievement']);
