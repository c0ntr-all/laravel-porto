<?php declare(strict_types=1);

use App\Containers\MovieSection\Person\UI\Actions\ListPersonsAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/persons', ListPersonsAction::class)
    ->middleware(['auth:api']);
