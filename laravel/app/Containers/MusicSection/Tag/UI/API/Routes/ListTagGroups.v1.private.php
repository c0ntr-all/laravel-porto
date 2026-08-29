<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\ListTagGroupsAction;
use Illuminate\Support\Facades\Route;

Route::get('music/tag-groups', ListTagGroupsAction::class)
     ->middleware(['auth:api']);
