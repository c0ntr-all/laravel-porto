<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Data\DTO;

use App\Ship\Parents\DTO\Data;

class FranchiseCreateData extends Data
{
    public int $user_id;
    public string $name;
    public ?string $description = null;
    public ?string $image = null;
    public ?int $order = null;

    public function __construct()
    {
    }
}
