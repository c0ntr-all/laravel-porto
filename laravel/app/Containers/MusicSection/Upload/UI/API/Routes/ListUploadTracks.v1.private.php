<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\ListUploadTracksAction;
use Illuminate\Support\Facades\Route;

Route::get('music/uploads/{upload}/tracks', ListUploadTracksAction::class)
     ->middleware(['auth:api']);
