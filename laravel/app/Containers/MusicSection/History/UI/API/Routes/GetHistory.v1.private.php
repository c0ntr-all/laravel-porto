<?php declare(strict_types=1);

use App\Containers\MusicSection\History\UI\Actions\GetHistoryAction;
use Illuminate\Support\Facades\Route;

Route::get('music/history/{history}', GetHistoryAction::class)
     ->middleware(['auth:api']);
