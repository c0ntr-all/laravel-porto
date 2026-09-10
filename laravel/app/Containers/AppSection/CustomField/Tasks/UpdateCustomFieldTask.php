<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldUpdateData;
use App\Containers\AppSection\CustomField\Data\Repositories\CustomFieldRepository;
use App\Containers\AppSection\CustomField\Models\CustomField;

class UpdateCustomFieldTask
{
    public function __construct(
        protected CustomFieldRepository $repository,
    ) {
    }

    public function run(CustomField $customField, CustomFieldUpdateData $dto): CustomField
    {
        return $this->repository->update($customField, $dto);
    }
}
