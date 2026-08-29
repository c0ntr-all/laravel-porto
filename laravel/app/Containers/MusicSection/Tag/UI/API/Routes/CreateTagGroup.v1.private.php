<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\CreateTagGroupAction;
use Illuminate\Support\Facades\Route;

Route::post('music/tag-groups', CreateTagGroupAction::class)
     ->middleware(['auth:api']);
