<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\UnmarkSeasonWatchedAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/seasons/{season}/watch', UnmarkSeasonWatchedAction::class)
    ->middleware(['auth:api']);
