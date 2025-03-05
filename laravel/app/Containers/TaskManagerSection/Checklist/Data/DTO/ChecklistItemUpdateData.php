<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\Checklist\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;

class ChecklistItemUpdateData extends Data
{
    public string|Optional $title;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i:s')]
    public Carbon|Optional|null $finished_at;

    public function __construct(
    ) {
    }
}
