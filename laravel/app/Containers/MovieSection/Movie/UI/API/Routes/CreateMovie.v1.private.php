<?php declare(strict_types=1);

use App\Containers\MovieSection\Movie\UI\Actions\CreateMovieAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/movies', CreateMovieAction::class)
    ->middleware(['auth:api']);
