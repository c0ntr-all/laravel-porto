<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\UnpinNoteAction;
use Illuminate\Support\Facades\Route;

Route::delete('notes/{note}/pin', UnpinNoteAction::class)
    ->middleware(['auth:api']);
