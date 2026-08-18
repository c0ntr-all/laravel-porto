<?php declare(strict_types=1);

use App\Containers\LifelogSection\Preset\UI\Actions\CreatePresetAction;
use Illuminate\Support\Facades\Route;

Route::post('lifelog/presets', CreatePresetAction::class)
     ->middleware(['auth:api']);
