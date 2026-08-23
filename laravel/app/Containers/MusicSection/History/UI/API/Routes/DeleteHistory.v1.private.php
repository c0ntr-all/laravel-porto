<?php declare(strict_types=1);

use App\Containers\MusicSection\History\UI\Actions\DeleteHistoryAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/history/{history}', DeleteHistoryAction::class)
     ->middleware(['auth:api']);
