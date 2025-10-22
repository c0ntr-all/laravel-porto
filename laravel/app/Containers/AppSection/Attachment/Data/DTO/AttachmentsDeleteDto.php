<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Data\DTO;

use App\Ship\Parents\DTO\Data;

class AttachmentsDeleteDto extends Data
{
    public array $deleted_attachments_ids = [];

    public function __construct(
    ) {
    }
}
