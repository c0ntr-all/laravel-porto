<?php

declare(strict_types=1);

use App\Containers\GameSection\Game\UI\Actions\ListGamesAction;
use Illuminate\Support\Facades\Route;

Route::get('games', ListGamesAction::class)
    ->middleware(['auth:api']);
