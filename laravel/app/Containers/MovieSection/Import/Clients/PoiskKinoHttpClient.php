<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Clients;

use App\Containers\MovieSection\Import\Contracts\KinopoiskMovieApiClientInterface;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskBlockedException;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskFilmNotFoundException;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskImportException;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskParseException;
use App\Containers\MovieSection\Import\Support\KinopoiskApiUrl;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class PoiskKinoHttpClient implements KinopoiskMovieApiClientInterface
{
    public function fetch(int $kpId): KinopoiskApiResponseDto
    {
        $token = config('movie_import.token');
        if (!is_string($token) || $token === '') {
            throw new KinopoiskImportException(
                'KINOPOISK_DEV_API_KEY is not configured.',
                503,
                null,
                null,
                ['reason' => 'missing_api_key'],
            );
        }

        $url = KinopoiskApiUrl::movie($kpId);

        try {
            $response = Http::retry(
                (int) config('movie_import.retries', 3),
                (int) config('movie_import.retry_sleep_ms', 400),
                static fn ($exception): bool => $exception instanceof ConnectionException,
                false,
            )
                ->timeout((int) config('movie_import.timeout', 30))
                ->connectTimeout((int) config('movie_import.connect_timeout', 15))
                ->withoutRedirecting()
                ->withOptions([
                    'version' => 1.1,
                    'force_ip_resolve' => 'v4',
                    'curl' => [
                        CURLOPT_IPRESOLVE => CURL_IPRESOLVE_V4,
                        CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                    ],
                ])
                ->withHeaders([
                    'X-API-KEY' => $token,
                    'Accept' => 'application/json',
                ])
                ->get($url);
        } catch (ConnectionException $exception) {
            throw new KinopoiskImportException(
                'PoiskKino API connection timed out. Retry later or check outbound HTTPS from the app container.',
                504,
                null,
                $exception,
                [
                    'reason' => 'connection_timeout',
                    'kinopoisk' => [
                        'requested_url' => $url,
                        'http_status' => null,
                    ],
                ],
            );
        }

        $context = [
            'kinopoisk' => $this->snapshot($response, $url),
        ];

        if ($response->notFound()) {
            throw new KinopoiskFilmNotFoundException($kpId, ['reason' => 'not_found', ...$context]);
        }

        if (in_array($response->status(), [401, 403, 429], true)) {
            throw new KinopoiskBlockedException(
                'PoiskKino API rejected the request with HTTP '.$response->status().'.',
                ['reason' => 'blocked', ...$context],
            );
        }

        if ($response->failed()) {
            throw new KinopoiskParseException(
                'PoiskKino API failed with HTTP '.$response->status().'.',
                ['reason' => 'http_error', ...$context],
            );
        }

        $payload = $response->json();
        if (!is_array($payload)) {
            throw new KinopoiskParseException('PoiskKino API returned invalid JSON.', [
                'reason' => 'invalid_json',
                ...$context,
            ]);
        }

        return new KinopoiskApiResponseDto(
            kp_id: $kpId,
            url: $url,
            http_status: $response->status(),
            payload: $payload,
            body: (string) $response->body(),
            headers: $response->headers(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    private function snapshot(Response $response, string $url): array
    {
        $body = (string) $response->body();
        $preview = mb_strlen($body) > 4000 ? mb_substr($body, 0, 4000).'…' : $body;

        return [
            'requested_url' => $url,
            'http_status' => $response->status(),
            'content_type' => $response->header('Content-Type'),
            'body_length' => strlen($body),
            'body_preview' => $preview,
        ];
    }
}
