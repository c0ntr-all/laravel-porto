<?php

declare(strict_types=1);

use App\Containers\AppSection\SteamUser\UI\Actions\GetSteamUserAction;
use Illuminate\Support\Facades\Route;

Route::get('steam-user', GetSteamUserAction::class)
    ->middleware(['auth:api']);
