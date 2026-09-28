<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\UpdateFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/franchises/{franchise}', UpdateFranchiseAction::class)
    ->middleware(['auth:api']);
