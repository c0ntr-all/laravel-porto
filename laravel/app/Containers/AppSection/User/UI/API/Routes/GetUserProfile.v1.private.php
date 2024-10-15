<?php declare(strict_types=1);

use App\Containers\AppSection\User\UI\Actions\GetUserProfileAction;
use Illuminate\Support\Facades\Route;

Route::get('profile', GetUserProfileAction::class)
    ->middleware(['auth:api']);
