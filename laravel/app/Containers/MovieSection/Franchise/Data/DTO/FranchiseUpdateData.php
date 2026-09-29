<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class FranchiseUpdateData extends Data
{
    public string|Optional $name;
    public string|Optional|null $description;
    public string|Optional|null $image;
    public int|Optional $order;

    public function __construct()
    {
    }
}
