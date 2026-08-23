<?php declare(strict_types=1);

use App\Containers\MusicSection\History\UI\Actions\CreateHistoryAction;
use Illuminate\Support\Facades\Route;

Route::post('music/history', CreateHistoryAction::class)
     ->middleware(['auth:api']);
