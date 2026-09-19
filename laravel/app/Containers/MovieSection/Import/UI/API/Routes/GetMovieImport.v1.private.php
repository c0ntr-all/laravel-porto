<?php declare(strict_types=1);

use App\Containers\MovieSection\Import\UI\Actions\GetMovieImportAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/imports/{movieImport}', GetMovieImportAction::class)
    ->middleware(['auth:api']);
