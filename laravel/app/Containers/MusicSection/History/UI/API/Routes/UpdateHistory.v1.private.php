<?php declare(strict_types=1);

use App\Containers\MusicSection\History\UI\Actions\UpdateHistoryAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/history/{history}', UpdateHistoryAction::class)
     ->middleware(['auth:api']);
