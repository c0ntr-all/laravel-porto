<?php declare(strict_types=1);

namespace App\Containers\AppSection\Tag\Data\DTO;

use App\Ship\Parents\DTO\Data;

class TagCreateDto extends Data
{
    public int $user_id;
    public string $name;
    public ?string $slug = null;
    public ?string $description = null;
    public ?string $icon = null;
    public ?string $color = null;
    public ?int $parent_id = null;

    /** @deprecated use description */
    public ?string $content = null;

    public function __construct()
    {
    }
}
