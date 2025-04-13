<?php declare(strict_types=1);

namespace App\Containers\TaskManagerSection\TaskProgress\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Optional;

class TaskProgressUpdateData extends Data
{
    public string|Optional $title;
    public string|Optional $content;
    public bool|Optional $is_final;
    public Carbon|Optional|null $finished_at;

    public function __construct(
    ) {
    }
}
