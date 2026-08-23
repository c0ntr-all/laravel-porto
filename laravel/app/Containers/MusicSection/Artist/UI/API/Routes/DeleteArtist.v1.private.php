<?php declare(strict_types=1);

use App\Containers\MusicSection\Artist\UI\Actions\DeleteArtistAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/artists/{artist}', DeleteArtistAction::class)
     ->middleware(['auth:api']);
