<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Tests\Unit;

use App\Containers\GameSection\Steam\Data\DTO\SteamGameAchievementDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamPlayerAchievementDto;
use PHPUnit\Framework\TestCase;

class SteamAchievementDtoTest extends TestCase
{
    public function test_maps_game_achievement_schema_fields(): void
    {
        $dto = SteamGameAchievementDto::fromApi([
            'internal_name' => 'Spend_Energy',
            'localized_name' => 'Стопка за стопкой, за стопкой',
            'localized_desc' => 'Потратьте 150 000 энергии.',
            'icon' => 'ff9f928e37583a0eb0d02095756f2b5508aa0499.jpg',
            'icon_gray' => 'ff9f928e37583a0eb0d02095756f2b5508aa0499.jpg',
            'hidden' => false,
            'player_percent_unlocked' => '14.4',
            'internal_key' => 4864,
            'min_progress_int' => 0,
            'max_progress_int' => 150000,
            'groupid' => 0,
            'archived' => false,
            'progress_type' => 1,
        ]);

        $this->assertSame('Spend_Energy', $dto->internal_name);
        $this->assertSame('Стопка за стопкой, за стопкой', $dto->localized_name);
        $this->assertSame('14.4', $dto->player_percent_unlocked);
        $this->assertSame(4864, $dto->internal_key);
        $this->assertSame(150000, $dto->max_progress_int);
        $this->assertSame(1, $dto->progress_type);
        $this->assertFalse($dto->hidden);
        $this->assertFalse($dto->archived);
    }

    public function test_maps_player_achievement_fields(): void
    {
        $dto = SteamPlayerAchievementDto::fromApi([
            'apiname' => 'Spend_Energy',
            'achieved' => 1,
            'unlocktime' => 1792241916,
            'name' => 'Стопка за стопкой, за стопкой',
            'description' => 'Потратьте 150 000 энергии.',
        ]);

        $this->assertSame('Spend_Energy', $dto->apiname);
        $this->assertTrue($dto->achieved);
        $this->assertSame(1792241916, $dto->unlocktime);
        $this->assertSame('Стопка за стопкой, за стопкой', $dto->name);
    }
}
