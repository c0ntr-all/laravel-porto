<?php declare(strict_types=1);

use App\Containers\AppSection\Document\Enums\DocumentMimeEnum;
use App\Ship\Enums\ContainerAliasEnum;

return [
    'default' => [
        'allowed_mimes' => array_merge(
            [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'video/mp4',
                'video/quicktime',
                'video/x-msvideo',
                'video/x-matroska',
                'video/3gpp',
            ],
            DocumentMimeEnum::values(),
        ),
        'max_file_size' => 51200,
    ],
];
