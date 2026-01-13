<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class PostCreateDto extends Data
{
    public int $user_id;
    public ?string $title = null;
    public ?string $content = null;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d')]
    public Carbon $date;
    #[WithCast(DateTimeInterfaceCast::class, format: 'H:i')]
    public ?Carbon $time;
    public ?array $tags = null;
    public ?array $new_tags = null;
    public ?array $attachments = [];

    public function __construct(
    ) {
    }
}
