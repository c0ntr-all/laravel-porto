<?php declare(strict_types=1);

use App\Containers\LifelogSection\Preset\UI\Actions\GetPresetAction;
use Illuminate\Support\Facades\Route;

Route::get('lifelog/presets/{preset}', GetPresetAction::class)
     ->middleware(['auth:api']);
