<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Upload\Data\DTO;

use Spatie\LaravelData\Data;

class CreateMusicUploadHistoryDto extends Data
{
    public array $data;

    public function __construct(
    ) {
    }
}
