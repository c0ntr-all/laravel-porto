<?php

use App\Containers\AppSection\Authentication\UI\Actions\LoginAction;
use Illuminate\Support\Facades\Route;

Route::post('login', LoginAction::class);
