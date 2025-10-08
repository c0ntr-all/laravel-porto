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
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i')]
    public Carbon $datetime;
    public array $tags;
    public array $new_tags;

    public function __construct(
    ) {
    }
}
