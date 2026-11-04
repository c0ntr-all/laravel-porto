<?php declare(strict_types=1);

use App\Containers\GallerySection\Image\UI\Actions\ListComputerFolderAction;
use Illuminate\Support\Facades\Route;

Route::get('gallery/library/folders', ListComputerFolderAction::class)
    ->middleware(['auth:api']);
