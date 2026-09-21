<?php declare(strict_types=1);

use App\Containers\MovieSection\Person\UI\Actions\GetPersonAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/persons/{person}', GetPersonAction::class)
    ->middleware(['auth:api']);
