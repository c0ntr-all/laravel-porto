<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\ListFranchiseMoviesAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/franchises/{franchise}/movies', ListFranchiseMoviesAction::class)
    ->middleware(['auth:api']);
