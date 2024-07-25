<?php declare(strict_types=1);

use App\Containers\Music\UI\Actions\Artists\GetArtistAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists/{artist}', GetArtistAction::class)
     ->middleware(['auth:api']);
