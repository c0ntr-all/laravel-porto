<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\CreateAlbumAction;
use Illuminate\Support\Facades\Route;

Route::post('music/albums', CreateAlbumAction::class)
     ->middleware(['auth:api']);
