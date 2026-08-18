<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\DTO;

use App\Containers\LifelogSection\Preset\Data\ValueObjects\PresetRules;
use App\Ship\Parents\DTO\Data;

class PresetCreateDto extends Data
{
    public int $user_id;
    public string $title;
    public string $color;
    public ?string $description = null;
    public ?string $icon = null;
    public ?array $tags = null;
    public PresetRules $rules;

    public function __construct()
    {
    }
}
