<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\DeleteFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/franchises/{franchise}', DeleteFranchiseAction::class)
    ->middleware(['auth:api']);
