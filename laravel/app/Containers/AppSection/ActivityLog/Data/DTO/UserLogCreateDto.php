<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\DTO;

use App\Ship\Parents\DTO\Data;

class UserLogCreateDto extends Data
{
    public int|string $user_id;
    public ?string $correlation_uuid = null;
    public string $loggable_type;
    public string $loggable_id;
    public string $event_type;

    public function __construct(
    ) {}
}
