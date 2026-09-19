<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskImportException;
use Throwable;

class MovieImportLogMeta
{
    /**
     * @return array<string, mixed>
     */
    public static function from(
        ?KinopoiskPageDto $page,
        ?ParsedKinopoiskFilmDto $parsed,
        ?Throwable $exception,
        int $apiStatus,
    ): array {
        $context = $exception instanceof KinopoiskImportException ? $exception->context : [];
        $kinopoisk = $context['kinopoisk'] ?? ($page !== null ? (new KinopoiskResponseInspector())->snapshot(
            html: $page->html,
            requestedUrl: $page->url,
            finalUrl: $page->final_url,
            httpStatus: $page->http_status,
            headers: $page->headers,
        ) : null);

        $meta = [
            'api_status' => $apiStatus,
            'stage' => self::stage($page, $parsed, $exception),
            'kinopoisk' => $kinopoisk,
            'attempts' => $context['attempts'] ?? $page?->attempts ?? [],
            'parser' => $context['parser'] ?? ($parsed !== null ? [
                'sources' => $parsed->parser_sources,
            ] : null),
            'extracted' => $context['extracted'] ?? ($parsed !== null ? [
                'title' => $parsed->title,
                'year' => $parsed->year,
                'type' => $parsed->type->value,
                'kp_img' => $parsed->kp_img,
                'kp_rating' => $parsed->kp_rating,
                'description' => $parsed->description,
                'genres' => array_map(static fn ($genre) => $genre->name, $parsed->genres),
                'countries' => $parsed->countries,
            ] : null),
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
            $meta['reason'] = $context['reason'] ?? null;
        }

        return array_filter($meta, static fn (mixed $value) => $value !== null && $value !== []);
    }

    private static function stage(
        ?KinopoiskPageDto $page,
        ?ParsedKinopoiskFilmDto $parsed,
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
}
