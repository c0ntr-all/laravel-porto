<?php declare(strict_types=1);

use App\Containers\AppSection\Authentication\UI\Actions\LogoutAction;
use Illuminate\Support\Facades\Route;

Route::post('logout', LogoutAction::class)
    ->middleware(['auth:api']);
