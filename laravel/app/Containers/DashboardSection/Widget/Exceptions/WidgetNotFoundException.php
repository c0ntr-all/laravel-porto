<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Exceptions;

use RuntimeException;

class WidgetNotFoundException extends RuntimeException
{
    public function __construct(string $type)
    {
        parent::__construct("Widget type [{$type}] is not registered.");
    }
}
