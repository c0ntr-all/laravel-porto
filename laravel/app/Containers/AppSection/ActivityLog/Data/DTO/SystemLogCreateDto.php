<?php declare(strict_types=1);

namespace App\Containers\AppSection\ActivityLog\Data\DTO;

use App\Ship\Parents\DTO\Data;

class SystemLogCreateDto extends Data
{
    public string $user_id;
    public string $event_type;
    public string $correlation_uuid;
    public string $main_type;
    public string $main_id;
    public ?string $related_type = null;
    public ?string $related_id = null;
    public ?array $metadata = null;

    public function __construct(
    ) {}
}
