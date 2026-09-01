<?php declare(strict_types=1);

use App\Containers\AppSection\Document\Enums\DocumentMimeEnum;
use App\Ship\Enums\ContainerAliasEnum;

return [
    ContainerAliasEnum::TM_TASK->value => [
        'allowed_mimes' => array_merge(
            [
                'image/jpeg',
                'image/png',
                'image/gif',
                'image/webp',
                'video/mp4',
            ],
            DocumentMimeEnum::values(),
        ),
        'max_file_size' => 20480,
    ],
];
