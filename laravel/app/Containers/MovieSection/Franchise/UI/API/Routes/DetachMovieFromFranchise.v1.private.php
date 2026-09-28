<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\DetachMovieFromFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/franchises/{franchise}/movies/{movie}', DetachMovieFromFranchiseAction::class)
    ->middleware(['auth:api']);
