<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\GetTagAction;
use Illuminate\Support\Facades\Route;

Route::get('music/tags/{tag:slug}', GetTagAction::class)
     ->middleware(['auth:api']);
