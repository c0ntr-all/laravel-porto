<?php

declare(strict_types=1);

use App\Containers\GameSection\Game\UI\Actions\CreateGameAction;
use Illuminate\Support\Facades\Route;

Route::post('games', CreateGameAction::class)
    ->middleware(['auth:api']);
