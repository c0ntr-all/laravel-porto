<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Data\DTO;

use App\Containers\MovieSection\Import\Enums\MovieImportStatusEnum;
use App\Ship\Parents\DTO\Data;

class MovieImportCreateData extends Data
{
    public int $user_id;
    public int $kp_id;
    public string $source_url;
    public MovieImportStatusEnum $status = MovieImportStatusEnum::Pending;

    public function __construct()
    {
    }
}
