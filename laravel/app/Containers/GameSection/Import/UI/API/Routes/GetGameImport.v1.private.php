<?php

declare(strict_types=1);

use App\Containers\GameSection\Import\UI\Actions\GetGameImportAction;
use Illuminate\Support\Facades\Route;

Route::get('games/imports/{gameImport}', GetGameImportAction::class)
    ->middleware(['auth:api'])
    ->whereNumber('gameImport');
