<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\Actions;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\Tasks\CreateActivitySystemLogTask;
use App\Ship\Parents\Actions\BaseAction;

final class SystemLogCreateAction extends BaseAction
{

    /**
     * Create a new job instance.
     */

    public function __construct(
        private readonly CreateActivitySystemLogTask $createActivitySystemLogTask
    )
    {
    }

    /**
     * Execute the job.
     */
    public function handle(SystemLogCreateDto $dto): void
    {
        $this->createActivitySystemLogTask->run($dto);
    }
}
