<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Preset\Data\DTO;

use App\Containers\LifelogSection\Preset\Data\ValueObjects\PresetRules;
use App\Ship\Parents\DTO\Data;
use Spatie\LaravelData\Optional;

class PresetUpdateDto extends Data
{
    public int $user_id;
    public string|Optional $title;
    public string|Optional|null $description;
    public string|Optional $color;
    public string|Optional|null $icon;
    public PresetRules|Optional $rules;

    public function __construct()
    {
    }
}
