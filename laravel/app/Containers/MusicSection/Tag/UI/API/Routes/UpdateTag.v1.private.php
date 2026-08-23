<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\UpdateTagAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/tags/{tag}', UpdateTagAction::class)
     ->middleware(['auth:api']);
