<?php declare(strict_types=1);

use App\Containers\LifelogSection\Preset\UI\Actions\DeletePresetAction;
use Illuminate\Support\Facades\Route;

Route::delete('lifelog/presets/{preset}', DeletePresetAction::class)
     ->middleware(['auth:api']);
