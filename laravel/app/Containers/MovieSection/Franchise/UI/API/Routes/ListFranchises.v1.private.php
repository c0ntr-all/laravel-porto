<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\ListFranchisesAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/franchises', ListFranchisesAction::class)
    ->middleware(['auth:api']);
