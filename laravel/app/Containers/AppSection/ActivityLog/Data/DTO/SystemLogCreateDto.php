<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SystemLogCreateDto extends Data
{
    public string $user_id;
    public string $event_type;
    public string $correlation_uuid;
    public string $loggable_type;
    public string $loggable_id;

    public function __construct(
    ) {}
}
