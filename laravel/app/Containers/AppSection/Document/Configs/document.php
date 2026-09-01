<?php declare(strict_types=1);

use App\Containers\AppSection\Document\Enums\DocumentMimeEnum;

return [
    'disk' => env('DOCUMENT_DISK', 'public'),

    'allowed_mimes' => DocumentMimeEnum::values(),

    'max_file_size' => 51200,

    'path_mask' => 'userfiles/{user_id}/documents/{file_id}.{extension}',
];
