<?php declare(strict_types=1);

use App\Containers\MovieSection\Franchise\UI\Actions\CreateFranchiseAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/franchises', CreateFranchiseAction::class)
    ->middleware(['auth:api']);
