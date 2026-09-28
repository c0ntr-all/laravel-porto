<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\UpdateMovieOrderInFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/franchises/{franchise}/movies/{movie}', UpdateMovieOrderInFranchiseAction::class)
    ->middleware(['auth:api']);
