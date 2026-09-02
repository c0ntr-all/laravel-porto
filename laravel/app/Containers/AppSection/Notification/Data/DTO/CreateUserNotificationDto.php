<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Data\DTO;

use App\Ship\Parents\DTO\Data;

class CreateUserNotificationDto extends Data
{
    public int $user_id;
    public string $type;
    public string $title;
    public string $body;
    public array $data = [];

    public function __construct()
    {
    }
}
