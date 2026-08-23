<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Tasks;

use App\Containers\MusicSection\History\Data\Repositories\HistoryRepository;
use App\Ship\Parents\Tasks\Task as ParentTask;
use Illuminate\Pagination\CursorPaginator;

class ListHistoryTask extends ParentTask
{
    public function __construct(
        private readonly HistoryRepository $historyRepository
    )
    {
    }

    public function run(int $userId): CursorPaginator
    {
        return $this->historyRepository->getWithCursor($userId);
    }
}
