<?php declare(strict_types=1);

use App\Containers\MovieSection\Movie\UI\Actions\DeleteMovieAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/movies/{movie}', DeleteMovieAction::class)
    ->middleware(['auth:api']);
