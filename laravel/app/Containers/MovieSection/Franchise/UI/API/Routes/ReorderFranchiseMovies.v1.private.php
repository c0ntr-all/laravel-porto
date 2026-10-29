<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\ReorderFranchiseMoviesAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/franchises/{franchise}/movies/reorder', ReorderFranchiseMoviesAction::class)
    ->middleware(['auth:api']);
