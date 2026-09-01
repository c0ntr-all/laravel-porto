<?php declare(strict_types=1);

namespace App\Containers\AppSection\User\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Http\UploadedFile;

class UploadUserAvatarDto extends Data
{
    public UploadedFile $file;

    public function __construct()
    {
    }
}
