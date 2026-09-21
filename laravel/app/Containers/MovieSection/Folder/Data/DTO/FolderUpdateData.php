<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Folder\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class FolderUpdateData extends Data
{
    public string|Optional $name;

    public function __construct()
    {
    }
}
