<?php

declare(strict_types=1);

namespace App\Containers\AppSection\Steam\Clients;

use App\Containers\AppSection\Steam\Contracts\SteamWebApiClientInterface;
use App\Containers\AppSection\Steam\Data\DTO\SteamGetPlayerSummariesResponseDto;
use App\Containers\AppSection\Steam\Data\DTO\SteamPlayerSummaryDto;
use App\Containers\AppSection\Steam\Exceptions\SteamWebApiConfigurationException;
use App\Containers\AppSection\Steam\Exceptions\SteamWebApiException;
use App\Containers\AppSection\Steam\Support\SteamWebApiUrl;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class SteamWebApiHttpClient implements SteamWebApiClientInterface
{
    public function getPlayerSummaries(array $steamIds): SteamGetPlayerSummariesResponseDto
    {
        $steamIds = array_values(array_unique(array_filter(
            array_map(static fn (mixed $id): string => trim((string) $id), $steamIds),
            static fn (string $id): bool => $id !== '',
        )));

        if ($steamIds === []) {
            throw new InvalidArgumentException('At least one Steam ID is required.');
        }

        $url = SteamWebApiUrl::playerSummaries($steamIds);
        $response = $this->get($url);
        $payload = $this->assertSuccessfulJson($response);

        $rawPlayers = $payload['response']['players'] ?? null;
        if (! is_array($rawPlayers)) {
            throw new SteamWebApiException('Steam GetPlayerSummaries returned an invalid players payload.');
        }

        $players = [];
        foreach ($rawPlayers as $player) {
            if (! is_array($player)) {
                continue;
            }

            $players[] = SteamPlayerSummaryDto::fromApiPlayer($player);
        }

        return new SteamGetPlayerSummariesResponseDto(
            url: $this->redactKey($url),
            http_status: $response->status(),
            players: $players,
        );
    }

    private function get(string $url): Response
    {
        $key = config('steam_web_api.key');
        if (! is_string($key) || $key === '') {
            throw new SteamWebApiConfigurationException;
        }

        try {
            return Http::retry(
                max(1, (int) config('steam_web_api.retries', 2)),
                (int) config('steam_web_api.retry_sleep_ms', 400),
                static fn ($exception): bool => $exception instanceof ConnectionException,
                false,
            )
                ->timeout((int) config('steam_web_api.timeout', 30))
                ->connectTimeout((int) config('steam_web_api.connect_timeout', 15))
                ->acceptJson()
                ->get($url);
        } catch (ConnectionException $exception) {
            throw new SteamWebApiException(
                'Steam Web API connection timed out.',
                504,
                $exception,
            );
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function assertSuccessfulJson(Response $response): array
    {
        if (in_array($response->status(), [401, 403], true)) {
            throw new SteamWebApiException(
                'Steam Web API rejected the request with HTTP '.$response->status().'.',
                $response->status(),
            );
        }

        if ($response->failed()) {
            throw new SteamWebApiException(
                'Steam Web API failed with HTTP '.$response->status().'.',
                $response->status() >= 500 ? 502 : 422,
            );
        }

        $payload = $response->json();
        if (! is_array($payload)) {
            throw new SteamWebApiException('Steam Web API returned invalid JSON.');
        }

        return $payload;
    }

    private function redactKey(string $url): string
    {
        return (string) preg_replace('/([?&]key=)[^&]*/i', '$1***', $url);
    }
}
