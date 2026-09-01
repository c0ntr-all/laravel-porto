<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\UpdateWidgetAction;
use Illuminate\Support\Facades\Route;

Route::patch('dashboards/{dashboard}/widgets/{widget}', UpdateWidgetAction::class)
     ->middleware(['auth:api']);
