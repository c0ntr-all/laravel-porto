<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\GetUploadAction;
use Illuminate\Support\Facades\Route;

Route::get('music/uploads/{upload}', GetUploadAction::class)
     ->middleware(['auth:api']);
