<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Clients;

use App\Containers\MovieSection\Import\Contracts\KinopoiskFilmPageClientInterface;
use App\Containers\MovieSection\Import\Data\DTO\KinopoiskPageDto;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskBlockedException;
use App\Containers\MovieSection\Import\Exceptions\KinopoiskFilmNotFoundException;
use App\Containers\MovieSection\Import\Support\KinopoiskResponseInspector;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class KinopoiskHttpClient implements KinopoiskFilmPageClientInterface
{
    public function __construct(
        private readonly KinopoiskResponseInspector $inspector,
    ) {
    }

    public function fetch(int $kpId): KinopoiskPageDto
    {
        $attempts = [];

        foreach ((array) config('movie_import.paths', ['film', 'series']) as $path) {
            $url = rtrim((string) config('movie_import.base_url'), '/').'/'.$path.'/'.$kpId.'/';
            $response = $this->request($url);
            $snapshot = $this->inspector->fromResponse($response, $url);
            $attempts[] = $snapshot;

            if ($this->isBlocked($response)) {
                throw new KinopoiskBlockedException(context: [
                    'reason' => 'blocked',
                    'kinopoisk' => $snapshot,
                    'attempts' => $attempts,
                ]);
            }

            if ($response->notFound()) {
                continue;
            }

            if ($response->failed()) {
                $context = [
                    'reason' => in_array($response->status(), [403, 429], true) ? 'blocked' : 'http_error',
                    'kinopoisk' => $snapshot,
                    'attempts' => $attempts,
                ];

                if (in_array($response->status(), [403, 429], true)) {
                    throw new KinopoiskBlockedException(
                        'Kinopoisk rejected the request with HTTP '.$response->status().'.',
                        $context,
                    );
                }

                throw new KinopoiskFilmNotFoundException($kpId, $context);
            }

            return new KinopoiskPageDto(
                kp_id: $kpId,
                url: $url,
                http_status: $response->status(),
                html: (string) $response->body(),
                final_url: $snapshot['final_url'],
                content_type: $snapshot['content_type'],
                headers: $response->headers(),
                attempts: $attempts,
            );
        }

        throw new KinopoiskFilmNotFoundException($kpId, [
            'reason' => 'not_found',
            'kinopoisk' => $attempts[array_key_last($attempts)] ?? null,
            'attempts' => $attempts,
        ]);
    }

    private function request(string $url): Response
    {
        $headers = [
            'User-Agent' => (string) config('movie_import.user_agent'),
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/avif,image/webp,*/*;q=0.8',
            'Accept-Language' => 'ru-RU,ru;q=0.9,en-US;q=0.8,en;q=0.7',
            'Referer' => rtrim((string) config('movie_import.base_url'), '/').'/',
            'Cache-Control' => 'no-cache',
        ];

        $cookie = config('movie_import.cookie');
        if (is_string($cookie) && $cookie !== '') {
            $headers['Cookie'] = $cookie;
        }

        return Http::timeout((int) config('movie_import.timeout', 20))
            ->connectTimeout((int) config('movie_import.connect_timeout', 5))
            ->withHeaders($headers)
            ->withOptions([
                'verify' => false,
                'allow_redirects' => true,
            ])
            ->get($url);
    }

    private function isBlocked(Response $response): bool
    {
        $signals = $this->inspector->signals((string) $response->body());

        return $signals['looks_blocked'] === true;
    }
}
