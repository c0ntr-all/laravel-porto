<?php declare(strict_types=1);

use App\Containers\MovieSection\Profession\UI\Actions\GetProfessionAction;
use Illuminate\Support\Facades\Route;

Route::get('movie/professions/{profession}', GetProfessionAction::class)
    ->middleware(['auth:api']);
