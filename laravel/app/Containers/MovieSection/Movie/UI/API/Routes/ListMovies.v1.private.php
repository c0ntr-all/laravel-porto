<?php declare(strict_types=1);

use App\Containers\MovieSection\Movie\UI\Actions\ListMoviesAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/movies', ListMoviesAction::class)
    ->middleware(['auth:api']);
