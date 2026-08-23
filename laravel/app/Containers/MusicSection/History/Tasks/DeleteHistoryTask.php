<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Tasks;

use App\Containers\MusicSection\History\Data\Repositories\HistoryRepository;
use App\Containers\MusicSection\History\Models\History;
use App\Ship\Parents\Tasks\Task as ParentTask;

class DeleteHistoryTask extends ParentTask
{
    public function __construct(
        private readonly HistoryRepository $historyRepository
    )
    {
    }

    public function run(History $history): ?bool
    {
        return $this->historyRepository->delete($history);
    }
}
