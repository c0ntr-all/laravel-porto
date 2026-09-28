<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\AttachMovieToFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/franchises/{franchise}/movies', AttachMovieToFranchiseAction::class)
    ->middleware(['auth:api']);
