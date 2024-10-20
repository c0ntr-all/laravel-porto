<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\UploadMusicAction;
use Illuminate\Support\Facades\Route;

Route::post('music/upload', UploadMusicAction::class)
     ->middleware(['auth:api']);
