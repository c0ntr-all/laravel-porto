<?php declare(strict_types=1);

use App\Containers\MovieSection\Person\UI\Actions\CreatePersonAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/persons', CreatePersonAction::class)
    ->middleware(['auth:api']);
