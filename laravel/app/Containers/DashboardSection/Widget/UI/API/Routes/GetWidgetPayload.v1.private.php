<?php declare(strict_types=1);

use App\Containers\DashboardSection\Widget\UI\Actions\GetWidgetPayloadAction;
use Illuminate\Support\Facades\Route;

Route::get('dashboards/{dashboard}/widgets/{widget}/payload', GetWidgetPayloadAction::class)
     ->middleware(['auth:api']);
