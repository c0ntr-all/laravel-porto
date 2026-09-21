<?php declare(strict_types=1);

use App\Containers\MovieSection\Person\UI\Actions\UpdatePersonAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/persons/{person}', UpdatePersonAction::class)
    ->middleware(['auth:api']);
