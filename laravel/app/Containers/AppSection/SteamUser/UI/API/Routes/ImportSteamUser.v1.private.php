<?php

declare(strict_types=1);

use App\Containers\AppSection\SteamUser\UI\Actions\ImportSteamUserAction;
use Illuminate\Support\Facades\Route;

Route::post('steam-user/import', ImportSteamUserAction::class)
    ->middleware(['auth:api']);
