<?php declare(strict_types=1);

use App\Containers\MusicSection\Upload\UI\Actions\ListUploadsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/uploads', ListUploadsAction::class)
     ->middleware(['auth:api']);
