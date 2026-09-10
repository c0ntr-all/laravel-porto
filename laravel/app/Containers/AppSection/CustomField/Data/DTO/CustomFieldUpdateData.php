<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class CustomFieldUpdateData extends Data
{
    public array|Optional $payload;
    public int|Optional $position;

    public function __construct()
    {
    }
}
