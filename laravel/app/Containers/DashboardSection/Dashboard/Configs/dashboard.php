<?php declare(strict_types=1);

return [
    'default_name' => 'Главная',

    /*
    | Widgets seeded onto a user's first dashboard.
    | Unknown types are skipped, so optional sections stay optional.
    */
    'default_widgets' => [
        [
            'type' => 'dashboard.welcome',
            'size' => 'full',
        ],
        [
            'type' => 'task-manager.open-tasks',
            'size' => 'third',
        ],
        [
            'type' => 'lifelog.recent-posts',
            'size' => 'third',
        ],
        [
            'type' => 'music.recent-history',
            'size' => 'third',
        ],
        [
            'type' => 'gallery.recent-images',
            'size' => 'half',
        ],
        [
            'type' => 'gallery.albums',
            'size' => 'half',
        ],
    ],
];
