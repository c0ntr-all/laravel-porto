<?php declare(strict_types=1);

use App\Containers\MusicSection\Album\UI\Actions\UpdateAlbumAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/albums/{album}', UpdateAlbumAction::class)
     ->middleware(['auth:api']);
