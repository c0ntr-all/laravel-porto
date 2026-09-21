<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Data\DTO;

use App\Ship\Parents\DTO\Data;

class FolderCreateData extends Data
{
    public int $user_id;
    public string $name;

    public function __construct()
    {
    }
}
