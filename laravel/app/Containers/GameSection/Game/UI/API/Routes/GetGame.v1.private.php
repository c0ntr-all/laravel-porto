<?php

declare(strict_types=1);

use App\Containers\GameSection\Game\UI\Actions\GetGameAction;
use Illuminate\Support\Facades\Route;

Route::get('games/{game}', GetGameAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
