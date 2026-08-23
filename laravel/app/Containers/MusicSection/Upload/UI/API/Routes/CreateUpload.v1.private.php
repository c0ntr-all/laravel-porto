<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\CreateUploadAction;
use Illuminate\Support\Facades\Route;

Route::post('music/uploads', CreateUploadAction::class)
     ->middleware(['auth:api']);
