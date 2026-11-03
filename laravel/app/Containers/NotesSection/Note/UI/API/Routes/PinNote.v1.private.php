<?php

declare(strict_types=1);

use App\Containers\NotesSection\Note\UI\Actions\PinNoteAction;
use Illuminate\Support\Facades\Route;

Route::post('notes/{note}/pin', PinNoteAction::class)
    ->middleware(['auth:api']);
