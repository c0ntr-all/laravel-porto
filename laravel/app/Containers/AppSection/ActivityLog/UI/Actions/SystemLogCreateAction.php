<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\UI\Actions;

use App\Containers\AppSection\ActivityLog\Data\DTO\SystemLogCreateDto;
use App\Containers\AppSection\ActivityLog\Tasks\CreateActivitySystemLogTask;
use Lorisleiva\Actions\Concerns\AsAction;

final class SystemLogCreateAction
{
    use AsAction;

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
