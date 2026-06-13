<?php declare(strict_types=1);

use App\Containers\LifelogSection\Period\UI\Actions\ListPeriodsAction;
use Illuminate\Support\Facades\Route;

Route::get('lifelog/periods', ListPeriodsAction::class)
     ->middleware(['auth:api']);
