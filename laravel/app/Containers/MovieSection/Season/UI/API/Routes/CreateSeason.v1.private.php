<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\CreateSeasonAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/seasons', CreateSeasonAction::class)
    ->middleware(['auth:api']);
