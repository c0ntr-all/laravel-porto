<?php declare(strict_types=1);

use App\Containers\DashboardSection\Dashboard\UI\Actions\ListDashboardsAction;
use Illuminate\Support\Facades\Route;

Route::get('dashboards', ListDashboardsAction::class)
     ->middleware(['auth:api']);
