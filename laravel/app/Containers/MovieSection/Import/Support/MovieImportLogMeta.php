<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Containers\MovieSection\Import\Data\DTO\ParsedKinopoiskFilmDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskImportException;
use Throwable;

class MovieImportLogMeta
{
    /**
     * @return array<string, mixed>
     */
    public static function from(
        ?KinopoiskApiResponseDto $response,
        ?ParsedKinopoiskFilmDto $parsed,
        ?Throwable $exception,
        int $apiStatus,
    ): array {
        $context = $exception instanceof KinopoiskImportException ? $exception->context : [];
        $kinopoisk = $context['kinopoisk'] ?? ($response !== null ? [
            'requested_url' => $response->url,
            'http_status' => $response->http_status,
            'payload_keys' => array_keys($response->payload),
            'body_preview' => mb_strlen($response->body) > 4000
                ? mb_substr($response->body, 0, 4000).'…'
                : $response->body,
        ] : null);

        $meta = [
            'api_status' => $apiStatus,
            'stage' => self::stage($response, $parsed, $exception),
            'source' => 'poiskkino',
            'kinopoisk' => $kinopoisk,
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
        ?KinopoiskApiResponseDto $response,
        ?ParsedKinopoiskFilmDto $parsed,
        ?Throwable $exception,
    ): string {
        if ($exception === null && $parsed !== null) {
            return 'persisted';
        }
        if ($parsed !== null) {
            return 'persist';
        }
        if ($response !== null) {
            return 'parse';
        }

        return 'fetch';
    }
}
