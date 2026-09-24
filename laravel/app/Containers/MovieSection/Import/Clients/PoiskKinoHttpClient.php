<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Clients;

use App\Containers\MovieSection\Import\Contracts\KinopoiskMovieApiClientInterface;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskApiResponseDto;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskSeasonsResponseDto;
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
    private const MAX_SEASON_PAGES = 100;

    public function fetch(int $kpId): KinopoiskApiResponseDto
    {
        $url = KinopoiskApiUrl::movie($kpId);
        $response = $this->get($url);

        $context = [
            'kinopoisk' => $this->snapshot($response, $url),
        ];

        if ($response->notFound()) {
            throw new KinopoiskFilmNotFoundException($kpId, ['reason' => 'not_found', ...$context]);
        }

        $payload = $this->assertSuccessfulJson($response, $url, $context);

        return new KinopoiskApiResponseDto(
            kp_id: $kpId,
            url: $url,
            http_status: $response->status(),
            payload: $payload,
            body: (string) $response->body(),
            headers: $response->headers(),
        );
    }

    public function fetchSeasonsByMovieId(int $movieKpId): KinopoiskSeasonsResponseDto
    {
        $limit = max(1, min(250, (int) config('movie_import.season_page_limit', 250)));
        $firstUrl = KinopoiskApiUrl::season([
            'movieId' => $movieKpId,
            'limit' => $limit,
            'sortField' => 'number',
            'sortType' => '1',
        ]);

        $docs = [];
        $pageUrls = [];
        $next = null;
        $httpStatus = 200;
        $pagesFetched = 0;

        do {
            $url = $next === null
                ? $firstUrl
                : KinopoiskApiUrl::season([
                    'movieId' => $movieKpId,
                    'limit' => $limit,
                    'sortField' => 'number',
                    'sortType' => '1',
                    'next' => $next,
                ]);

            $response = $this->get($url);
            $context = [
                'kinopoisk' => $this->snapshot($response, $url),
            ];
            $payload = $this->assertSuccessfulJson($response, $url, $context);

            $pageDocs = $payload['docs'] ?? null;
            if (!is_array($pageDocs)) {
                throw new KinopoiskParseException('PoiskKino season API returned invalid docs payload.', [
                    'reason' => 'invalid_docs',
                    ...$context,
                ]);
            }

            foreach ($pageDocs as $doc) {
                if (is_array($doc)) {
                    $docs[] = $doc;
                }
            }

            $pageUrls[] = $url;
            $httpStatus = $response->status();
            $pagesFetched++;

            $hasNext = (bool) ($payload['hasNext'] ?? false);
            $nextValue = $payload['next'] ?? null;
            $next = $hasNext && is_string($nextValue) && $nextValue !== ''
                ? $nextValue
                : null;
        } while ($next !== null && $pagesFetched < self::MAX_SEASON_PAGES);

        return new KinopoiskSeasonsResponseDto(
            movie_kp_id: $movieKpId,
            url: $firstUrl,
            http_status: $httpStatus,
            docs: $docs,
            pages_fetched: $pagesFetched,
            page_urls: $pageUrls,
        );
    }

    private function get(string $url): Response
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

        try {
            return Http::retry(
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
    }

    /**
     * @param array<string, mixed> $context
     * @return array<string, mixed>
     */
    private function assertSuccessfulJson(Response $response, string $url, array $context): array
    {
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
                'kinopoisk' => $this->snapshot($response, $url),
            ]);
        }

        return $payload;
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
