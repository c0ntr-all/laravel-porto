<?php declare(strict_types=1);

use App\Containers\AppSection\User\UI\Actions\UpdateUserProfileAction;
use Illuminate\Support\Facades\Route;

Route::patch('profile', UpdateUserProfileAction::class)
    ->middleware(['auth:api']);
