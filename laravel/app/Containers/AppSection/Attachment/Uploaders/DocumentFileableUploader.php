<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Uploaders;

use App\Containers\AppSection\Attachment\Contracts\FileableUploaderInterface;
use App\Containers\AppSection\Attachment\Data\DTO\FileableReferenceDto;
use App\Containers\AppSection\Document\Enums\DocumentMimeEnum;
use App\Containers\AppSection\Document\Tasks\CreateDocumentFromUploadTask;
use App\Ship\Enums\ContainerAliasEnum;
use Illuminate\Http\UploadedFile;

class DocumentFileableUploader implements FileableUploaderInterface
{
    public function __construct(
        private readonly CreateDocumentFromUploadTask $createDocumentFromUploadTask,
    ) {
    }

    public function supports(string $mimeType): bool
    {
        return in_array($mimeType, DocumentMimeEnum::values(), true);
    }

    public function upload(UploadedFile $file, int $userId): FileableReferenceDto
    {
        $document = $this->createDocumentFromUploadTask->run($file, $userId);

        return FileableReferenceDto::from([
            'fileable_type' => ContainerAliasEnum::APP_DOCUMENT->value,
            'fileable_id' => (string) $document->id,
        ]);
    }
}
