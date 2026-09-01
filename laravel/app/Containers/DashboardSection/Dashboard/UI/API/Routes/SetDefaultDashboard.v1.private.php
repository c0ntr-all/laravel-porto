<?php declare(strict_types=1);

use App\Containers\DashboardSection\Dashboard\UI\Actions\SetDefaultDashboardAction;
use Illuminate\Support\Facades\Route;

Route::post('dashboards/{dashboard}/default', SetDefaultDashboardAction::class)
     ->middleware(['auth:api']);
