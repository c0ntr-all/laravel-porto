<?php declare(strict_types=1);

use App\Containers\DashboardSection\Dashboard\UI\Actions\CreateDashboardAction;
use Illuminate\Support\Facades\Route;

Route::post('dashboards', CreateDashboardAction::class)
     ->middleware(['auth:api']);
