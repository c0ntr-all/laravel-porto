<?php declare(strict_types=1);

use App\Containers\MusicSection\Artist\UI\Actions\CreateArtistAction;
use Illuminate\Support\Facades\Route;

Route::post('music/artists', CreateArtistAction::class)
     ->middleware(['auth:api']);
