<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\DeleteTagGroupAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/tag-groups/{tagGroup}', DeleteTagGroupAction::class)
     ->middleware(['auth:api']);
