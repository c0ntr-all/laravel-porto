<?php

declare(strict_types=1);

use App\Containers\GameSection\Achievement\UI\Actions\CreateAchievementAction;
use Illuminate\Support\Facades\Route;

Route::post('games/{game}/achievements', CreateAchievementAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
