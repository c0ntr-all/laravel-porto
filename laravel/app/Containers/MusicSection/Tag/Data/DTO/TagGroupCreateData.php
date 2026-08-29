<?php declare(strict_types=1);

namespace App\Containers\MusicSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagGroupCreateData extends Data
{
    public string $name;
    public ?string $slug = null;
    public ?string $description = null;
    public bool $is_system = false;
    public bool $is_active = true;
    public int $display_order = 0;

    public function __construct()
    {
    }
}
