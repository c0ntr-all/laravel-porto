<?php declare(strict_types=1);

use App\Containers\MovieSection\Import\UI\Actions\ImportMovieFromKinopoiskAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/imports', ImportMovieFromKinopoiskAction::class)
    ->middleware(['auth:api']);
