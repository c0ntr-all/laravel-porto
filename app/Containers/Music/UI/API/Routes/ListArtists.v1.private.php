<?php declare(strict_types=1);

use App\Containers\Music\UI\Actions\Artists\ListArtistsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/artists', ListArtistsAction::class)
     ->middleware(['auth:api']);
