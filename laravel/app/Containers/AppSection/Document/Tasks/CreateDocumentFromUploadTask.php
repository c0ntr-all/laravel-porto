<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Tasks;

use App\Containers\AppSection\Document\Data\DTO\CreateDocumentDto;
use App\Containers\AppSection\Document\Data\Repositories\DocumentRepository;
use App\Containers\AppSection\Document\Models\Document;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Http\UploadedFile;

class CreateDocumentFromUploadTask extends ParentTask
{
    public function __construct(
        private readonly SaveUploadedDocumentTask $saveUploadedDocumentTask,
        private readonly CreateDocumentTask $createDocumentTask,
    ) {
    }

    public function run(UploadedFile $file, int $userId): Document
    {
        $payload = $this->saveUploadedDocumentTask->run($file, $userId);

        return $this->createDocumentTask->run(CreateDocumentDto::from($payload));
    }
}
