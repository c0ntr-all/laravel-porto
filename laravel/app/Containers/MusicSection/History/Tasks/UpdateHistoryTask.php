<?php declare(strict_types=1);

namespace App\Containers\MusicSection\History\Tasks;

use App\Containers\MusicSection\History\Data\DTO\UpdateHistoryDto;
use App\Containers\MusicSection\History\Data\Repositories\HistoryRepository;
use App\Containers\MusicSection\History\Models\History;
use App\Ship\Parents\Tasks\Task as ParentTask;

class UpdateHistoryTask extends ParentTask
{
    public function __construct(
        private readonly HistoryRepository $historyRepository
    )
    {
    }

    public function run(History $history, UpdateHistoryDto $dto): History
    {
        return $this->historyRepository->update($history, $dto);
    }
}
