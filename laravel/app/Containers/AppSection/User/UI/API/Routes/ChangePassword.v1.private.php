<?php declare(strict_types=1);

use App\Containers\AppSection\User\UI\Actions\ChangePasswordAction;
use Illuminate\Support\Facades\Route;

Route::put('profile/password', ChangePasswordAction::class)
    ->middleware(['auth:api']);
