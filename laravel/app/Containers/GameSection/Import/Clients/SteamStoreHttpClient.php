<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Clients;

use App\Containers\GameSection\Import\Contracts\SteamStoreClientInterface;
use App\Containers\GameSection\Import\Data\DTO\SteamStorePageDto;
use App\Containers\GameSection\Import\Exceptions\SteamStoreException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreGameNotFoundException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreParseException;
use App\Containers\GameSection\Import\Support\SteamStoreUrl;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SteamStoreHttpClient implements SteamStoreClientInterface
{
    public function fetchAppPage(int $steamId): SteamStorePageDto
    {
        $url = SteamStoreUrl::app($steamId);
        $response = $this->get($url, $steamId);

        if ($response->status() === 404) {
            throw new SteamStoreGameNotFoundException($steamId);
        }

        if ($response->failed()) {
            throw new SteamStoreParseException(
                'Steam store page failed with HTTP '.$response->status().'.',
            );
        }

        $html = (string) $response->body();
        if (trim($html) === '') {
            throw new SteamStoreParseException('Steam store page is empty.');
        }

        return new SteamStorePageDto(
            steam_id: $steamId,
            url: $url,
            http_status: $response->status(),
            html: $html,
        );
    }

    private function get(string $url, int $steamId): Response
    {
        try {
            return Http::retry(
                max(1, (int) config('steam_store.retries', 2)),
                (int) config('steam_store.retry_sleep_ms', 400),
                static fn ($exception): bool => $exception instanceof ConnectionException,
                false,
            )
                ->timeout((int) config('steam_store.timeout', 30))
                ->connectTimeout((int) config('steam_store.connect_timeout', 15))
                ->withHeaders([
                    'User-Agent' => (string) config('steam_store.user_agent'),
                    'Accept' => 'text/html,application/xhtml+xml',
                    'Accept-Language' => 'en-US,en;q=0.9',
                    'Cookie' => 'birthtime=0; lastagecheckage=1-January-1970; wants_mature_content=1; mature_content=1',
                ])
                ->get($url);
        } catch (ConnectionException $exception) {
            throw new SteamStoreException(
                'Steam store connection timed out for app '.$steamId.'.',
                504,
                null,
                $exception,
            );
        }
    }
}
