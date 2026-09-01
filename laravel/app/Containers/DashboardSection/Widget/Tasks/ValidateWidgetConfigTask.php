<?php declare(strict_types=1);

namespace App\Containers\DashboardSection\Widget\Tasks;

use App\Containers\DashboardSection\Widget\Contracts\WidgetContract;
use App\Containers\DashboardSection\Widget\Enums\WidgetSizeEnum;
use App\Containers\DashboardSection\Widget\Exceptions\InvalidWidgetConfigException;
use App\Ship\Parents\Tasks\Task as ParentTask;

class ValidateWidgetConfigTask extends ParentTask
{
    /**
     * @param array<string, mixed> $config
     * @return array<string, mixed>
     */
    public function run(WidgetContract $widget, array $config, ?WidgetSizeEnum $size = null): array
    {
        if ($size !== null && !in_array($size, $widget->supportedSizes(), true)) {
            throw new InvalidWidgetConfigException(
                "Size [{$size->value}] is not supported by widget [{$widget->type()}].",
            );
        }

        $normalized = [];

        foreach ($widget->configSchema() as $key => $rules) {
            $value = $config[$key] ?? ($rules['default'] ?? null);
            $required = (bool) ($rules['required'] ?? false);
            $empty = $value === null || $value === '';

            if ($required && $empty) {
                throw new InvalidWidgetConfigException(
                    "Config [{$key}] is required for widget [{$widget->type()}].",
                );
            }

            if ($empty) {
                $normalized[$key] = null;
                continue;
            }

            $normalized[$key] = $this->cast($value, $rules);
        }

        return $normalized;
    }

    /**
     * @param array<string, mixed> $rules
     */
    private function cast(mixed $value, array $rules): mixed
    {
        $type = (string) ($rules['type'] ?? 'string');

        return match ($type) {
            'integer' => $this->castInteger($value, $rules),
            'boolean' => filter_var($value, FILTER_VALIDATE_BOOL, FILTER_NULL_ON_FAILURE) ?? false,
            'html', 'text', 'string' => is_scalar($value) ? (string) $value : '',
            default => $value,
        };
    }

    /**
     * @param array<string, mixed> $rules
     */
    private function castInteger(mixed $value, array $rules): int
    {
        $int = (int) $value;
        $min = isset($rules['min']) ? (int) $rules['min'] : null;
        $max = isset($rules['max']) ? (int) $rules['max'] : null;

        if ($min !== null) {
            $int = max($min, $int);
        }
        if ($max !== null) {
            $int = min($max, $int);
        }

        return $int;
    }
}
