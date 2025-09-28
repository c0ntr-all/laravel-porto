<?php declare(strict_types=1);

use App\Containers\AppSection\Tag\UI\Actions\DeleteTagAction;
use Illuminate\Support\Facades\Route;

Route::delete('tags/{tag}', DeleteTagAction::class)
     ->middleware(['auth:api']);
