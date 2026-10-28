<?php

declare(strict_types=1);

use App\Containers\GameSection\Game\UI\Actions\UpdateGameAction;
use Illuminate\Support\Facades\Route;

Route::patch('games/{game}', UpdateGameAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
