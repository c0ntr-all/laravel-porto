<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\GetAlbumAction;
use Illuminate\Support\Facades\Route;

Route::get('music/albums/{album}', GetAlbumAction::class)
     ->middleware(['auth:api']);
