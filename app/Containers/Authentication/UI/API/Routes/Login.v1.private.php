<?php

use App\Containers\Authentication\Actions\LoginAction;
use Illuminate\Support\Facades\Route;

Route::post('login', LoginAction::class);
