<?php declare(strict_types=1);

namespace App\Containers\AppSection\CustomField\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CustomFieldCreateData extends Data
{
    public int $user_id;
    public int|string $fieldable_id;
    public string $fieldable_type;
    public string $type;
    public array $payload;
    public ?int $position = null;

    public function __construct()
    {
    }
}
