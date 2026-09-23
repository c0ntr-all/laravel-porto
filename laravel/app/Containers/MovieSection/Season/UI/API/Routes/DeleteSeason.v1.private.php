<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\DeleteSeasonAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/seasons/{season}', DeleteSeasonAction::class)
    ->middleware(['auth:api']);
