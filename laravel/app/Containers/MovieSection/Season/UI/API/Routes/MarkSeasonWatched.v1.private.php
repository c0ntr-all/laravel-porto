<?php declare(strict_types=1);

use App\Containers\MovieSection\Season\UI\Actions\MarkSeasonWatchedAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/seasons/{season}/watch', MarkSeasonWatchedAction::class)
    ->middleware(['auth:api']);
