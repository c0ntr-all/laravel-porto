<?php declare(strict_types=1);

use App\Containers\MovieSection\Genre\UI\Actions\DeleteGenreAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/genres/{genre}', DeleteGenreAction::class)
    ->middleware(['auth:api']);
