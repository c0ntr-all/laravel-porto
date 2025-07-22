<?php declare(strict_types=1);

use App\Containers\AppSection\Registration\UI\Actions\RegisterUserAction;
use Illuminate\Support\Facades\Route;

Route::post('register', RegisterUserAction::class);
