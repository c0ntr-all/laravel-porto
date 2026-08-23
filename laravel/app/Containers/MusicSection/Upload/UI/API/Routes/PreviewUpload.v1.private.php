<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\PreviewUploadAction;
use Illuminate\Support\Facades\Route;

Route::post('music/uploads/previews', PreviewUploadAction::class)
     ->middleware(['auth:api']);
