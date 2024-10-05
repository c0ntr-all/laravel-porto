<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\CreateTagAction;
use Illuminate\Support\Facades\Route;

Route::post('music/tags', CreateTagAction::class)
     ->middleware(['auth:api']);
