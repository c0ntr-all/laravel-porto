<?php

use App\Containers\Authentication\Actions\LogoutAction;
use Illuminate\Support\Facades\Route;

Route::post('logout', LogoutAction::class)
    ->middleware(['auth:api']);
