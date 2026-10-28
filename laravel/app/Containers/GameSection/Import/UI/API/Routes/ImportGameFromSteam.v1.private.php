<?php

declare(strict_types=1);

use App\Containers\GameSection\Import\UI\Actions\ImportGameFromSteamAction;
use Illuminate\Support\Facades\Route;

Route::post('games/imports', ImportGameFromSteamAction::class)
    ->middleware(['auth:api']);

Route::post('games/import', ImportGameFromSteamAction::class)
    ->middleware(['auth:api']);
