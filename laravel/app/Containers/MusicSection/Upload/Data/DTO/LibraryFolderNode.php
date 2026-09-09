<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Data\DTO;

use App\Ship\Parents\DTO\Data;

class LibraryFolderNode extends Data
{
    public string $id;
    public string $name;
    public string $path;
    public bool $has_children;
    public bool $uploaded;

    public function __construct()
    {
    }
}
