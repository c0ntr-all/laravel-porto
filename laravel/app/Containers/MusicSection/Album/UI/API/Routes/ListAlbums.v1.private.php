<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\ListAlbumsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/albums', ListAlbumsAction::class)
     ->middleware(['auth:api']);
