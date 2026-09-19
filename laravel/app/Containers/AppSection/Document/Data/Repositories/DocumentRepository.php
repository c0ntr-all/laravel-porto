<?php declare(strict_types=1);

namespace App\Containers\AppSection\Document\Data\Repositories;

use App\Containers\AppSection\Document\Models\Document;

class DocumentRepository
{
    public function create(array $data): Document
    {
        return Document::create($data);
    }

    public function findForUser(string $id, int $userId): ?Document
    {
        return Document::query()
            ->where('user_id', $userId)
            ->whereIdOrUuid($id)
            ->first();
    }
}
