<?php declare(strict_types=1);

use App\Containers\MusicSection\History\UI\Actions\ListHistoryAction;
use Illuminate\Support\Facades\Route;

Route::get('music/history', ListHistoryAction::class)
     ->middleware(['auth:api']);
