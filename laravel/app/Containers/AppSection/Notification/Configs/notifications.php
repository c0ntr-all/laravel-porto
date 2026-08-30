<?php declare(strict_types=1);

return [
    /*
    |--------------------------------------------------------------------------
    | Default notification channels
    |--------------------------------------------------------------------------
    |
    | Channels used when SendNotificationTask is called without an explicit list.
    | Add more keys here as new channel drivers are registered.
    |
    */
    'default_channels' => [
        'email',
    ],

    /*
    |--------------------------------------------------------------------------
    | Channel driver map
    |--------------------------------------------------------------------------
    |
    | key => concrete NotificationChannelInterface implementation
    |
    */
    'channels' => [
        'email' => \App\Containers\AppSection\Notification\Channels\EmailChannel::class,
        // 'telegram' => \App\Containers\AppSection\Notification\Channels\TelegramChannel::class,
        // 'push' => \App\Containers\AppSection\Notification\Channels\PushChannel::class,
    ],
];
