<?php declare(strict_types=1);

namespace App\Containers\MusicSection\UploadHistory\Data\Repositories;

use App\Containers\MusicSection\UploadHistory\Data\DTO\CreateMusicUploadHistoryDto;
use App\Containers\MusicSection\UploadHistory\Models\MusicUploadHistory;

class MusicUploadHistoryRepository
{
    public function create(CreateMusicUploadHistoryDto $dto)
    {
        return MusicUploadHistory::create(['data' => json_encode($dto->data, JSON_UNESCAPED_UNICODE)]);
    }
}
