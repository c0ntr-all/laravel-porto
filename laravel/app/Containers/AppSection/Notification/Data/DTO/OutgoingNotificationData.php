<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Data\DTO;

use App\Ship\Parents\DTO\Data;

class OutgoingNotificationData extends Data
{
    public function __construct(
        public string $subject,
        public string $body,
        public string $type = 'system',
        public array $data = [],
        public array $meta = [],
    ) {
    }
}
