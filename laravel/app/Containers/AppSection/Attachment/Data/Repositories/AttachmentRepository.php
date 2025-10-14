<?php declare(strict_types=1);

namespace App\Containers\AppSection\Attachment\Data\Repositories;

use App\Containers\AppSection\Attachment\Models\Attachment;

class AttachmentRepository
{
    /**
     * @param array $data
     * @return mixed
     */
    public function create(array $data): Attachment
    {
        return Attachment::create($data);
    }
}
