<?php declare(strict_types=1);

use App\Containers\MovieSection\Profession\UI\Actions\UpdateProfessionAction;
use Illuminate\Support\Facades\Route;

Route::patch('movie/professions/{profession}', UpdateProfessionAction::class)
    ->middleware(['auth:api']);
