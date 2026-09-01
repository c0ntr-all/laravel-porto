<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Data\ValueObjects;

use App\Containers\AppSection\User\Models\User;
use App\Containers\DashboardSection\Widget\Models\Widget;

final class WidgetContext
{
    /**
     * @param array<string, mixed> $config
     */
    public function __construct(
        public readonly User $user,
        public readonly Widget $widget,
        public readonly array $config = [],
    ) {
    }

    public function config(string $key, mixed $default = null): mixed
    {
        return data_get($this->config, $key, $default);
    }

    public function limit(int $default = 5, int $max = 50): int
    {
        return max(1, min($max, (int) $this->config('limit', $default)));
    }
}
