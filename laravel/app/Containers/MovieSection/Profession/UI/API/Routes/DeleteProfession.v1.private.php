<?php declare(strict_types=1);

use App\Containers\MovieSection\Profession\UI\Actions\DeleteProfessionAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/professions/{profession}', DeleteProfessionAction::class)
    ->middleware(['auth:api']);
