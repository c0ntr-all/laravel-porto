<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Services;

use App\Containers\AppSection\Attachment\Contracts\FileableUploaderInterface;
use App\Containers\AppSection\Attachment\Exceptions\UnsupportedAttachmentMimeTypeException;
use Illuminate\Http\UploadedFile;

class FileableUploaderRegistry
{
    /**
     * @param iterable<FileableUploaderInterface> $uploaders
     */
    public function __construct(
        private readonly iterable $uploaders,
    ) {
    }

    public function resolve(UploadedFile $file): FileableUploaderInterface
    {
        $mimeType = (string) ($file->getMimeType() ?: 'application/octet-stream');

        foreach ($this->uploaders as $uploader) {
            if ($uploader->supports($mimeType)) {
                return $uploader;
            }
        }

        throw new UnsupportedAttachmentMimeTypeException($mimeType);
    }
}
