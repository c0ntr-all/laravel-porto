<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;
use Spatie\LaravelData\Optional;

class PostUpdateDto extends Data
{
    public int $user_id;
    public string|Optional|null $title;
    public string|Optional|null $content;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i')]
    public Carbon|Optional|null $datetime;

    public function __construct(
    ) {
    }
}
