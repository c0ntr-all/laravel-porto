<?php declare(strict_types=1);

return [
    'default_channels' => [
        'database',
    ],

    'channels' => [
        'email' => \App\Containers\AppSection\Notification\Channels\EmailChannel::class,
        'database' => \App\Containers\AppSection\Notification\Channels\DatabaseChannel::class,
    ],

    'broadcast' => [
        'channel_prefix' => 'users',
        'channel_suffix' => 'notifications',
    ],

    'pagination' => [
        'per_page' => 20,
    ],
];
