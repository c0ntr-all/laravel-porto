<?php declare(strict_types=1);

namespace App\Containers\LifelogSection\Post\Data\DTO;

use App\Ship\Parents\DTO\Data;
use Illuminate\Support\Carbon;
use Spatie\LaravelData\Attributes\WithCast;
use Spatie\LaravelData\Casts\DateTimeInterfaceCast;

class PostCreateData extends Data
{
    public int $user_id;
    public ?string $title = null;
    public ?string $content = null;
    #[WithCast(DateTimeInterfaceCast::class, format: 'Y-m-d H:i')]
    public Carbon $datetime;

    public function __construct(
    ) {
    }
}
