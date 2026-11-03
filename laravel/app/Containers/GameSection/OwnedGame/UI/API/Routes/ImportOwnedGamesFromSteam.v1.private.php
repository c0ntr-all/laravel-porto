<?php

declare(strict_types=1);

use App\Containers\GameSection\OwnedGame\UI\Actions\ImportOwnedGamesFromSteamAction;
use Illuminate\Support\Facades\Route;

Route::post('owned-games/import', ImportOwnedGamesFromSteamAction::class)
    ->middleware(['auth:api']);
