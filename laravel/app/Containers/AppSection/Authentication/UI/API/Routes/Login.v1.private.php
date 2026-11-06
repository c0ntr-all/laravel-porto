<?php declare(strict_types=1);

use App\Containers\AppSection\Authentication\UI\Actions\LoginAction;
use Illuminate\Support\Facades\Route;

Route::post('login', LoginAction::class)->middleware('throttle:10,1');
