<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\ListAlbumTypesAction;
use Illuminate\Support\Facades\Route;

Route::get('music/album-types', ListAlbumTypesAction::class)
     ->middleware(['auth:api']);
