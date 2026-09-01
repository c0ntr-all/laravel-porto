<?php declare(strict_types=1);

use App\Containers\AppSection\User\UI\Actions\DeleteUserAvatarAction;
use Illuminate\Support\Facades\Route;

Route::delete('profile/avatar', DeleteUserAvatarAction::class)
    ->middleware(['auth:api']);
