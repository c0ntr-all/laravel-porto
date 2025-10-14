<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Http\UploadedFile;

class AttachmentsCreateDto extends Data
{
    public string $user_id;
    public string $attachable_type;
    public string $attachable_id;
    /**
     * @var UploadedFile[]
     */
    public array $files;

    public function __construct(
    ) {}
}
