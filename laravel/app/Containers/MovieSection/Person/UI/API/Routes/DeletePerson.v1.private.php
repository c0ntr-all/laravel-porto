<?php declare(strict_types=1);

use App\Containers\MovieSection\Person\UI\Actions\DeletePersonAction;
use Illuminate\Support\Facades\Route;

Route::delete('movie/persons/{person}', DeletePersonAction::class)
    ->middleware(['auth:api']);
