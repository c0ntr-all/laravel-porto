<?php declare(strict_types=1);

use App\Containers\MovieSection\Import\UI\Actions\ListMovieImportsAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/imports', ListMovieImportsAction::class)
    ->middleware(['auth:api']);
