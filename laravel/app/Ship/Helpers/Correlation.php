<?php declare(strict_types=1);

namespace App\Ship\Helpers;

use Illuminate\Support\Facades\App;
use Illuminate\Support\Str;

/**
 * Утилита для управления correlation UUID внутри одного запроса/UseCase.
 *
 * Позволяет:
 *  - сгенерировать новый correlation UUID при старте UseCase;
 *  - получить его из любого места (Observer, Job и т.д.);
 *  - использовать для связи разных процессов, например system_logs и user_logs.
 */
final class Correlation
{
    private const string CONTEXT_KEY = 'currentCorrelationId';

    public static function init(): string
    {
        $uuid = (string) Str::uuid();
        App::instance(self::CONTEXT_KEY, $uuid);

        return $uuid;
    }

    public static function set(string $uuid): void
    {
        App::instance(self::CONTEXT_KEY, $uuid);
    }

    public static function get(): ?string
    {
        return App::has(self::CONTEXT_KEY)
            ? App::get(self::CONTEXT_KEY)
            : null;
    }

    public static function clear(): void
    {
        if (App::has(self::CONTEXT_KEY)) {
            App::forgetInstance(self::CONTEXT_KEY);
        }
    }
}
