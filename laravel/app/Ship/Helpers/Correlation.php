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
    private const string UUID = 'currentCorrelationId';
    private const string USE_CASE = 'currentCorrelationUseCase';

    public static function init(): void
    {
        if (!self::getUuid()) {
            $uuid = (string) Str::uuid();
            App::instance(self::UUID, $uuid);
        }
    }

    public static function setUuid(string $uuid): void
    {
        App::instance(self::UUID, $uuid);
    }

    public static function getUuid(): ?string
    {
        return App::has(self::UUID)
            ? App::get(self::UUID)
            : null;
    }

    public static function setUseCase(string $useCase): void
    {
        App::instance(self::USE_CASE, $useCase);
    }

    public static function getUseCase(): ?string
    {
        return App::has(self::USE_CASE)
            ? App::get(self::USE_CASE)
            : null;
    }

    public static function makeUseCaseName(string $containerAliasValue, string $eventTypesValue): string
    {
        return $containerAliasValue . '.' . $eventTypesValue;
    }

    public static function isUseCase(string $containerAliasValue, string $eventTypesValue): bool
    {
        $expected = self::makeUseCaseName($containerAliasValue, $eventTypesValue);

        return $expected === self::getUseCase();
    }

    public static function clear(): void
    {
        if (App::has(self::UUID)) {
            App::forgetInstance(self::UUID);
        }
        if (App::has(self::USE_CASE)) {
            App::forgetInstance(self::USE_CASE);
        }
    }
}
