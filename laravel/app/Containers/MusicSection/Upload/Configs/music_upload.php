<?php declare(strict_types=1);

return [
    'disk' => env('MUSIC_LIBRARY_DISK', 'windows_f'),
    'drive' => env('MUSIC_LIBRARY_DRIVE', 'F'),
    'extensions' => ['mp3', 'm4a', 'flac', 'ogg', 'wav', 'aac'],
    'skip_directories' => ['FLAC'],
    'cover_filenames' => [
        'Cover.jpg',
        'Cover.jpeg',
        'Cover.png',
        'cover.jpg',
        'cover.jpeg',
        'cover.png',
        'folder.jpg',
        'folder.png',
        'Front.jpg',
        'front.jpg',
    ],
];
