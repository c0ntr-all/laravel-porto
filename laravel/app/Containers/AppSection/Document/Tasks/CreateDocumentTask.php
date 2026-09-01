<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Tasks;

use App\Containers\AppSection\Document\Data\DTO\CreateDocumentDto;
use App\Containers\AppSection\Document\Data\Repositories\DocumentRepository;
use App\Containers\AppSection\Document\Models\Document;
use App\Ship\Parents\Tasks\Task as ParentTask;

class CreateDocumentTask extends ParentTask
{
    public function __construct(
        private readonly DocumentRepository $documentRepository,
    ) {
    }

    public function run(CreateDocumentDto $dto): Document
    {
        return $this->documentRepository->create($dto->toArray());
    }
}
