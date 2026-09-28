<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\GetFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/franchises/{franchise}', GetFranchiseAction::class)
    ->middleware(['auth:api']);
