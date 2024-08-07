<?php declare(strict_types=1);

use App\Containers\MusicSection\Artist\UI\Actions\ListAlbumsByArtistAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}/albums', ListAlbumsByArtistAction::class)
     ->middleware(['auth:api']);
