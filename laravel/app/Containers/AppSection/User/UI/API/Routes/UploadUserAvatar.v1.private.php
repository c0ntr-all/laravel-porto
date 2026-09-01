<?php declare(strict_types=1);

use App\Containers\AppSection\User\UI\Actions\UploadUserAvatarAction;
use Illuminate\Support\Facades\Route;

Route::post('profile/avatar', UploadUserAvatarAction::class)
    ->middleware(['auth:api']);
