<?php declare(strict_types=1);

use App\Containers\MusicSection\Tag\UI\Actions\DeleteTagAction;
use Illuminate\Support\Facades\Route;

Route::delete('music/tags/{tag}', DeleteTagAction::class)
     ->middleware(['auth:api']);
