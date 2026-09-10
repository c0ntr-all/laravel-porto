<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Tasks;

use App\Containers\AppSection\CustomField\Data\DTO\CustomFieldListDto;
use App\Containers\AppSection\CustomField\Data\Repositories\CustomFieldRepository;
use App\Ship\Parents\Tasks\Task;
use Illuminate\Database\Eloquent\Collection;

class ListCustomFieldsTask extends Task
{
    public function __construct(
        private readonly CustomFieldRepository $customFieldRepository,
    ) {
    }

    public function run(CustomFieldListDto $dto): Collection
    {
        return $this->customFieldRepository->get($dto->toArray());
    }
}
