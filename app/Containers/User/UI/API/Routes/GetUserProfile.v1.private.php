<?php

use App\Containers\User\UI\Actions\GetUserProfileAction;
use Illuminate\Support\Facades\Route;

Route::get('profile', GetUserProfileAction::class)
    ->middleware(['auth:api']);
