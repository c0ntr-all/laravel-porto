<?php

declare(strict_types=1);

use App\Containers\GameSection\Import\UI\Actions\ListGameImportsAction;
use Illuminate\Support\Facades\Route;

Route::get('games/imports', ListGameImportsAction::class)
    ->middleware(['auth:api']);
