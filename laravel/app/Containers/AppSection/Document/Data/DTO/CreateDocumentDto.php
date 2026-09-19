<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateDocumentDto extends Data
{
    public string $uuid;
    public int $user_id;
    public string $original_name;
    public string $mime_type;
    public string $extension;
    public int $size;
    public string $disk;
    public string $path;

    public function __construct()
    {
    }
}
