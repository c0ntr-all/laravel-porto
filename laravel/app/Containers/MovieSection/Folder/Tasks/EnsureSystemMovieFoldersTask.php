<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Tasks;

use App\Containers\MovieSection\Folder\Enums\SystemMovieFolderEnum;
use App\Containers\MovieSection\Folder\Models\Folder;
use App\Ship\Parents\Tasks\Task as ParentTask;

class EnsureSystemMovieFoldersTask extends ParentTask
{
    public function run(int $userId): void
    {
        foreach (SystemMovieFolderEnum::cases() as $folder) {
            Folder::query()->firstOrCreate(
                [
                    'user_id' => $userId,
                    'slug' => $folder->value,
                ],
                [
                    'name' => $folder->folderName(),
                    'is_system' => true,
                    'movies_count' => 0,
                ],
            );
        }
    }
}
