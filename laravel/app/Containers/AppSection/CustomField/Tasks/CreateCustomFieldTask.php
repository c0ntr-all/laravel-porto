<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldCreateData;
use App\Containers\AppSection\CustomField\Data\Repositories\CustomFieldRepository;
use App\Containers\AppSection\CustomField\Models\CustomField;
use App\Ship\Exceptions\CreateResourceFailedException;
use App\Ship\Exceptions\RepositoryException;

class CreateCustomFieldTask
{
    public function __construct(
        protected CustomFieldRepository $repository,
    ) {
    }

    /**
     * @throws CreateResourceFailedException
     */
    public function run(CustomFieldCreateData $dto): CustomField
    {
        try {
            return $this->repository->create($dto);
        } catch (RepositoryException $exception) {
            throw new CreateResourceFailedException($exception->getMessage());
        }
    }
}
