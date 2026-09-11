<?php declare(strict_types=1);

use App\Containers\MovieSection\Movie\UI\Actions\GetMovieAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/movies/{movie}', GetMovieAction::class)
    ->middleware(['auth:api']);
