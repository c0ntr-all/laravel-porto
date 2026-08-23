<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateUploadDto extends Data
{
    public int $user_id;
    public string $path;

    public function __construct()
    {
    }
}
