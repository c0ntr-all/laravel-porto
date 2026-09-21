<?php declare(strict_types=1);

use App\Containers\MovieSection\Profession\UI\Actions\ListProfessionsAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/professions', ListProfessionsAction::class)
    ->middleware(['auth:api']);
