<?php declare(strict_types=1);

use App\Containers\LifelogSection\Preset\UI\Actions\ListPresetsAction;
use Illuminate\Support\Facades\Route;

Route::get('lifelog/presets', ListPresetsAction::class)
     ->middleware(['auth:api']);
