<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\ListTagsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/tags', ListTagsAction::class)
     ->middleware(['auth:api']);
