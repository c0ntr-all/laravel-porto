<?php declare(strict_types=1);

use App\Containers\MovieSection\Genre\UI\Actions\UpdateGenreAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/genres/{genre}', UpdateGenreAction::class)
    ->middleware(['auth:api']);
