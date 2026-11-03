<?php

declare(strict_types=1);

use App\Containers\GameSection\OwnedGame\UI\Actions\ListOwnedGamesAction;
use Illuminate\Support\Facades\Route;

Route::get('owned-games', ListOwnedGamesAction::class)
    ->middleware(['auth:api']);
