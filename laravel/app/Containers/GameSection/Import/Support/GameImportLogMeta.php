<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Support;

use App\Containers\GameSection\Import\Data\DTO\ParsedSteamGameDto;
use App\Containers\GameSection\Import\Data\DTO\SteamStorePageDto;
use App\Containers\GameSection\Import\Exceptions\SteamStoreException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreGameNotFoundException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreParseException;
use Throwable;

class GameImportLogMeta
{
    /**
     * @param  array<string, mixed>|null  $before
     * @param  array<string, mixed>|null  $after
     * @param  array<string, mixed>  $changes
     * @return array<string, mixed>
     */
    public static function from(
        ?SteamStorePageDto $page,
        ?ParsedSteamGameDto $parsed,
        ?Throwable $exception,
        int $apiStatus,
        ?array $before = null,
        ?array $after = null,
        array $changes = [],
        ?bool $wasCreated = null,
    ): array {
        $httpStatus = $page?->http_status;
        if ($httpStatus === null && $exception instanceof SteamStoreGameNotFoundException) {
            $httpStatus = 404;
        }

        $meta = [
            'api_status' => $apiStatus,
            'stage' => self::stage($page, $parsed, $exception),
            'source' => 'steam',
            'action' => $wasCreated === null ? null : ($wasCreated ? 'created' : 'updated'),
            'duplicate' => $wasCreated === false,
            'steam' => $page !== null || $httpStatus !== null ? array_filter([
                'requested_url' => $page?->url ?? $parsed?->source_url,
                'http_status' => $httpStatus,
                'body_preview' => $page !== null ? self::preview($page->html) : null,
            ], static fn (mixed $value) => $value !== null) : null,
            'before' => $before,
            'after' => $after,
            'changes' => $changes,
            'extracted' => $parsed !== null ? [
                'name' => $parsed->name,
                'description' => $parsed->description,
                'img' => $parsed->img,
                'air_date' => $parsed->air_date,
                'developer' => $parsed->developer,
                'publisher' => $parsed->publisher,
            ] : null,
            'reason' => self::reason($exception),
        ];

        if ($exception !== null) {
            $meta['exception'] = [
                'class' => $exception::class,
                'message' => $exception->getMessage(),
                'file' => $exception->getFile(),
                'line' => $exception->getLine(),
                'previous' => $exception->getPrevious() !== null ? [
                    'class' => $exception->getPrevious()::class,
                    'message' => $exception->getPrevious()->getMessage(),
                ] : null,
            ];
        }

        return array_filter($meta, static fn (mixed $value) => $value !== null);
    }

    private static function stage(
        ?SteamStorePageDto $page,
        ?ParsedSteamGameDto $parsed,
        ?Throwable $exception,
    ): string {
        if ($exception === null && $parsed !== null) {
            return 'persisted';
        }
        if ($parsed !== null) {
            return 'persist';
        }
        if ($page !== null) {
            return 'parse';
        }

        return 'fetch';
    }

    private static function reason(?Throwable $exception): ?string
    {
        return match (true) {
            $exception instanceof SteamStoreGameNotFoundException => 'not_found',
            $exception instanceof SteamStoreParseException => 'parse_error',
            $exception instanceof SteamStoreException && $exception->httpStatus === 504 => 'connection_timeout',
            $exception === null => null,
            default => 'import_failed',
        };
    }

    private static function preview(string $body): string
    {
        return mb_strlen($body) > 4000 ? mb_substr($body, 0, 4000).'…' : $body;
    }
}
