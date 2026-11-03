<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Steam\Clients;

use App\Containers\GameSection\Steam\Contracts\SteamWebApiClientInterface;
use App\Containers\GameSection\Steam\Data\DTO\SteamGameAchievementDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetGameAchievementsResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetOwnedGamesResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetPlayerAchievementsResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamGetPlayerSummariesResponseDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamOwnedGameDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamPlayerAchievementDto;
use App\Containers\GameSection\Steam\Data\DTO\SteamPlayerSummaryDto;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiConfigurationException;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiException;
use App\Containers\GameSection\Steam\Exceptions\SteamWebApiPlayerAchievementsException;
use App\Containers\GameSection\Steam\Support\SteamWebApiUrl;
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

    public function getGameAchievements(int $appId, ?string $language = null): SteamGetGameAchievementsResponseDto
    {
        if ($appId < 1) {
            throw new InvalidArgumentException('Steam app id must be a positive integer.');
        }

        $url = SteamWebApiUrl::gameAchievements($appId, $language);
        $response = $this->get($url);
        $payload = $this->assertSuccessfulJson($response);

        $rawAchievements = $payload['response']['achievements'] ?? null;
        if (! is_array($rawAchievements)) {
            throw new SteamWebApiException('Steam GetGameAchievements returned an invalid achievements payload.');
        }

        $achievements = [];
        foreach ($rawAchievements as $achievement) {
            if (! is_array($achievement)) {
                continue;
            }

            $achievements[] = SteamGameAchievementDto::fromApi($achievement);
        }

        return new SteamGetGameAchievementsResponseDto(
            app_id: $appId,
            url: $this->redactKey($url),
            http_status: $response->status(),
            achievements: $achievements,
        );
    }

    public function getPlayerAchievements(
        int $appId,
        string $steamId,
        ?string $language = null,
    ): SteamGetPlayerAchievementsResponseDto {
        if ($appId < 1) {
            throw new InvalidArgumentException('Steam app id must be a positive integer.');
        }

        $steamId = trim($steamId);
        if ($steamId === '') {
            throw new InvalidArgumentException('Steam ID is required.');
        }

        $url = SteamWebApiUrl::playerAchievements($appId, $steamId, $language);
        $response = $this->get($url);
        $payload = $this->assertSuccessfulJson($response);

        $playerstats = $payload['playerstats'] ?? null;
        if (! is_array($playerstats)) {
            throw new SteamWebApiException('Steam GetPlayerAchievements returned an invalid playerstats payload.');
        }

        $success = (bool) ($playerstats['success'] ?? false);
        if (! $success) {
            throw new SteamWebApiPlayerAchievementsException($appId, $steamId);
        }

        $rawAchievements = $playerstats['achievements'] ?? [];
        if (! is_array($rawAchievements)) {
            throw new SteamWebApiException('Steam GetPlayerAchievements returned an invalid achievements payload.');
        }

        $achievements = [];
        foreach ($rawAchievements as $achievement) {
            if (! is_array($achievement)) {
                continue;
            }

            $achievements[] = SteamPlayerAchievementDto::fromApi($achievement);
        }

        $gameName = $playerstats['gameName'] ?? null;

        return new SteamGetPlayerAchievementsResponseDto(
            app_id: $appId,
            steam_id: $steamId,
            url: $this->redactKey($url),
            http_status: $response->status(),
            success: true,
            game_name: is_string($gameName) && $gameName !== '' ? $gameName : null,
            achievements: $achievements,
        );
    }

    public function getOwnedGames(string $steamId): SteamGetOwnedGamesResponseDto
    {
        $steamId = trim($steamId);
        if ($steamId === '') {
            throw new InvalidArgumentException('Steam ID is required.');
        }

        $url = SteamWebApiUrl::ownedGames($steamId);
        $response = $this->get($url);
        $payload = $this->assertSuccessfulJson($response);

        $responsePayload = $payload['response'] ?? null;
        if (! is_array($responsePayload)) {
            throw new SteamWebApiException('Steam GetOwnedGames returned an invalid response payload.');
        }

        $rawGames = $responsePayload['games'] ?? [];
        if (! is_array($rawGames)) {
            throw new SteamWebApiException('Steam GetOwnedGames returned an invalid games payload.');
        }

        $games = [];
        foreach ($rawGames as $game) {
            if (! is_array($game)) {
                continue;
            }

            $games[] = SteamOwnedGameDto::fromApi($game);
        }

        $gameCount = $responsePayload['game_count'] ?? count($games);
        if (! is_numeric($gameCount)) {
            $gameCount = count($games);
        }

        return new SteamGetOwnedGamesResponseDto(
            steam_id: $steamId,
            url: $this->redactKey($url),
            http_status: $response->status(),
            game_count: (int) $gameCount,
            games: $games,
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
