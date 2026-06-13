<?php declare(strict_types=1);

use App\Containers\LifelogSection\Period\UI\Actions\CreatePeriodAction;
use Illuminate\Support\Facades\Route;

Route::post('lifelog/periods', CreatePeriodAction::class)
     ->middleware(['auth:api']);
