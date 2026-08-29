<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\UpdateTagGroupAction;
use Illuminate\Support\Facades\Route;

Route::patch('music/tag-groups/{tagGroup}', UpdateTagGroupAction::class)
     ->middleware(['auth:api']);
