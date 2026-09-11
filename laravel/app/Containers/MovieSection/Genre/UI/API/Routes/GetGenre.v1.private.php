<?php declare(strict_types=1);

use App\Containers\MovieSection\Genre\UI\Actions\GetGenreAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/genres/{genre}', GetGenreAction::class)
    ->middleware(['auth:api']);
