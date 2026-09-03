<?php declare(strict_types=1);

namespace App\Containers\AppSection\Notification\Data\DTO;

use App\Ship\Parents\DTO\Data;

class NotificationListDto extends Data
{
    public int $user_id;
    public ?bool $unread_only = null;
    public ?string $cursor = null;
    public int $per_page = 20;
    public int $page = 1;

    public function __construct()
    {
    }
}
