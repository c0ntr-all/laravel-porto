<?php declare(strict_types=1);

use App\Containers\MovieSection\Genre\UI\Actions\ListGenresAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/genres', ListGenresAction::class)
    ->middleware(['auth:api']);
