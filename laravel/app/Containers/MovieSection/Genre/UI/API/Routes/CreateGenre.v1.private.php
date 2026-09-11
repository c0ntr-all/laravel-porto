<?php declare(strict_types=1);

use App\Containers\MovieSection\Genre\UI\Actions\CreateGenreAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/genres', CreateGenreAction::class)
    ->middleware(['auth:api']);
