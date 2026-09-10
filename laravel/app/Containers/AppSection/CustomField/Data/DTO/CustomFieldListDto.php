<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CustomFieldListDto extends Data
{
    public int $user_id;

    public function __construct()
    {
    }
}
