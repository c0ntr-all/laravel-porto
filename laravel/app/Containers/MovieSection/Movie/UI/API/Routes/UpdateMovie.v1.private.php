<?php declare(strict_types=1);

use App\Containers\MovieSection\Movie\UI\Actions\UpdateMovieAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/movies/{movie}', UpdateMovieAction::class)
    ->middleware(['auth:api']);
