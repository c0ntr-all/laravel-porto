<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Exceptions;

use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;

class UnsupportedAttachmentMimeTypeException extends UnprocessableEntityHttpException
{
    public function __construct(string $mimeType)
    {
        parent::__construct("Unsupported attachment MIME type: {$mimeType}");
    }
}
