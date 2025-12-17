<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;

class PostUpdateContextDto extends Data
{
    public int $user_id;
    public string|Optional|null $title;
    public string|Optional|null $content;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public Carbon $date;
    #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
    public Carbon $time;
    public array|null $tags;
    public array|null $new_tags;
    public array $deleted_attachments_ids = [];

    public function __construct(
    ) {
    }
}
