<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\DeleteUploadAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/uploads/{upload}', DeleteUploadAction::class)
     ->middleware(['auth:api']);
