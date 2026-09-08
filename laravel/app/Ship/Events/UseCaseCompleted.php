<?php declare(strict_types=1);

namespace App\Ship\Events;

use Illuminate\Foundation\Events\Dispatchable;

final class UseCaseCompleted
{
    use Dispatchable;

    public function __construct(
        public readonly int $userId,
        public readonly string $loggableType,
        public readonly string $loggableId,
        public readonly string $eventType,
    ) {
    }
}
