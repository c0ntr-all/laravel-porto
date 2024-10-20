<?php declare(strict_types=1);

namespace App\Containers\MusicSection\UploadHistory\Data\Tasks;

use App\Containers\MusicSection\UploadHistory\Data\DTO\CreateMusicUploadHistoryDto;
use App\Containers\MusicSection\UploadHistory\Data\Repositories\MusicUploadHistoryRepository;
use App\Ship\Parents\Tasks\Task;

class CreateMusicUploadHistoryTask extends Task
{
    public function __construct(
        private readonly MusicUploadHistoryRepository $musicUploadHistoryRepository
    )
    {
    }

    public function run(CreateMusicUploadHistoryDto $dto)
    {
        return $this->musicUploadHistoryRepository->create($dto);
    }
}
