<?php declare(strict_types=1);

use App\Containers\LifelogSection\Preset\UI\Actions\UpdatePresetAction;
use Illuminate\Support\Facades\Route;

Route::patch('lifelog/presets/{preset}', UpdatePresetAction::class)
     ->middleware(['auth:api']);
