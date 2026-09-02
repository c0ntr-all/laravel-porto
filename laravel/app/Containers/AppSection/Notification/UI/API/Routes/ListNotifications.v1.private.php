<?php declare(strict_types=1);

use App\Containers\AppSection\Notification\UI\Actions\GetUnreadNotificationsCountAction;
use App\Containers\AppSection\Notification\UI\Actions\ListNotificationsAction;
use App\Containers\AppSection\Notification\UI\Actions\MarkAllNotificationsAsReadAction;
use App\Containers\AppSection\Notification\UI\Actions\MarkNotificationAsReadAction;
use Illuminate\Support\Facades\Route;

Route::get('app/notifications', ListNotificationsAction::class)
    ->middleware(['auth:api']);

Route::get('app/notifications/unread-count', GetUnreadNotificationsCountAction::class)
    ->middleware(['auth:api']);

Route::patch('app/notifications/read-all', MarkAllNotificationsAsReadAction::class)
    ->middleware(['auth:api']);

Route::patch('app/notifications/{notificationId}/read', MarkNotificationAsReadAction::class)
    ->middleware(['auth:api']);
