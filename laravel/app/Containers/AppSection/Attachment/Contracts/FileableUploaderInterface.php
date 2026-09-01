<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Contracts;

use App\Containers\AppSection\Attachment\Data\DTO\FileableReferenceDto;
use Illuminate\Http\UploadedFile;

interface FileableUploaderInterface
{
    public function supports(string $mimeType): bool;

    public function upload(UploadedFile $file, int $userId): FileableReferenceDto;
}
