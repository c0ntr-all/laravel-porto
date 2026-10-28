<?php

declare(strict_types=1);

use App\Containers\GameSection\Game\UI\Actions\DeleteGameAction;
use Illuminate\Support\Facades\Route;

Route::delete('games/{game}', DeleteGameAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('game');
