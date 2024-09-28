<?php declare(strict_types=1);

use App\Containers\MusicSection\Artist\UI\Actions\UpdateArtistAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/artists/{artist}', UpdateArtistAction::class)
     ->middleware(['auth:api']);
