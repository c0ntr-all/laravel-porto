<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Data\DTO;

use App\Ship\Parents\DTO\Data;

class FileableReferenceDto extends Data
{
    public string $fileable_type;
    public string $fileable_id;

    public function __construct()
    {
    }
}
