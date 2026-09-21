<?php declare(strict_types=1);

use App\Containers\MovieSection\Movie\UI\Actions\ListMovieCreditsAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/movies/{movie}/credits', ListMovieCreditsAction::class)
    ->middleware(['auth:api']);
