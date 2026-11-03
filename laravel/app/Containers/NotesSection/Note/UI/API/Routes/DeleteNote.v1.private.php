<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\DeleteNoteAction;
use Illuminate\Support\Facades\Route;

Route::delete('notes/{note}', DeleteNoteAction::class)
    ->middleware(['auth:api']);
