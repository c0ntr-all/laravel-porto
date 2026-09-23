<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\GetSeasonAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/seasons/{season}', GetSeasonAction::class)
    ->middleware(['auth:api']);
