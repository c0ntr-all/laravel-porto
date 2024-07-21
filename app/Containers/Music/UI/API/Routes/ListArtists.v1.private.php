<?php

use App\Containers\User\UI\Actions\GetUserProfileAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists', GetUserProfileAction::class)
    ->middleware(['auth:api']);
