<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Data\DTO;

use App\Ship\Parents\DTO\Data;

class AttachmentCreateDto extends Data
{
    public string $user_id;
    public string $attachable_type;
    public string $attachable_id;
    public string $fileable_type;
    public string $fileable_id;

    public function __construct(
    ) {}
}
