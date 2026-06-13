<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Period\Data\Repositories;

use App\Containers\LifelogSection\Period\Data\DTO\PeriodCreateDto;
use App\Containers\LifelogSection\Period\Models\Period;

class PeriodRepository
{
    public function createPeriod(PeriodCreateDto $dto): Period
    {
        return Period::create([
            'user_id' => $dto->user_id,
            'title' => $dto->title,
            'color' => $dto->color,
            'start_post_id' => $dto->start_post_id,
            'end_post_id' => $dto->end_post_id,
        ]);
    }
}
