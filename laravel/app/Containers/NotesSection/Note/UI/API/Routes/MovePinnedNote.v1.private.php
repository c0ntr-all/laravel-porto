<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\MovePinnedNoteAction;
use Illuminate\Support\Facades\Route;

Route::post('notes/{note}/pin-position', MovePinnedNoteAction::class)
    ->middleware(['auth:api']);
