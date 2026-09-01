<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\CreateWidgetAction;
use Illuminate\Support\Facades\Route;

Route::post('dashboards/{dashboard}/widgets', CreateWidgetAction::class)
     ->middleware(['auth:api']);
