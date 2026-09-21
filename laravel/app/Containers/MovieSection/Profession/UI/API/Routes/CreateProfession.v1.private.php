<?php declare(strict_types=1);

use App\Containers\MovieSection\Profession\UI\Actions\CreateProfessionAction;
use Illuminate\Support\Facades\Route;

Route::post('movie/professions', CreateProfessionAction::class)
    ->middleware(['auth:api']);
