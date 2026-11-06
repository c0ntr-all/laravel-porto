<?php declare(strict_types=1);

namespace App\Ship\Helpers;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;
use RuntimeException;

final class PublicHttps
{
    public const MAX_IMAGE_BYTES = 15 * 1024 * 1024;

    public const MAX_VIDEO_BYTES = 100 * 1024 * 1024;

    public static function get(string $url, int $maxBytes): string
    {
        $current = $url;

        for ($hop = 0; $hop < 3; $hop++) {
            self::assertPublic($current);

            $response = Http::timeout(30)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    'Accept' => '*/*',
                ])
                ->withOptions([
                    'verify' => true,
                    'allow_redirects' => false,
                ])
                ->get($current);

            if (in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                $current = self::redirectTarget($current, $response);
                continue;
            }

            if (!$response->successful()) {
                throw new RuntimeException('Unable to download the remote file.');
            }

            $body = $response->body();
            if (strlen($body) > $maxBytes) {
                throw new RuntimeException('Remote file is too large.');
            }

            return $body;
        }

        throw new RuntimeException('Too many redirects.');
    }

    public static function assertPublic(string $url): void
    {
        $parts = parse_url($url);
        if (!is_array($parts)) {
            throw new InvalidArgumentException('Only public https URLs are allowed.');
        }

        $scheme = strtolower((string) ($parts['scheme'] ?? ''));
        $host = (string) ($parts['host'] ?? '');
        $port = (int) ($parts['port'] ?? 443);

        if ($scheme !== 'https' || $host === '' || $port !== 443 || isset($parts['user']) || isset($parts['pass'])) {
            throw new InvalidArgumentException('Only public https URLs are allowed.');
        }

        foreach (self::resolveIps($host) as $ip) {
            if (filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
                throw new InvalidArgumentException('The URL host is not public.');
            }
        }
    }

    /**
     * @return list<string>
     */
    private static function resolveIps(string $host): array
    {
        $host = strtolower(rtrim($host, '.'));
        if ($host === 'localhost' || str_ends_with($host, '.localhost') || str_ends_with($host, '.local')) {
            throw new InvalidArgumentException('The URL host is not public.');
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return [$host];
        }

        $ips = [];
        $records = dns_get_record($host, DNS_A + DNS_AAAA);
        if (is_array($records)) {
            foreach ($records as $record) {
                if (isset($record['ip']) && is_string($record['ip'])) {
                    $ips[] = $record['ip'];
                }
                if (isset($record['ipv6']) && is_string($record['ipv6'])) {
                    $ips[] = $record['ipv6'];
                }
            }
        }

        if ($ips === []) {
            throw new InvalidArgumentException('The URL host could not be resolved.');
        }

        return $ips;
    }

    private static function redirectTarget(string $current, Response $response): string
    {
        $location = $response->header('Location');
        if (!is_string($location) || $location === '') {
            throw new RuntimeException('Remote redirect is missing a location.');
        }

        if (str_starts_with($location, 'https://')) {
            return $location;
        }

        $parts = parse_url($current);
        if (!is_array($parts) || !isset($parts['host'])) {
            throw new RuntimeException('Remote redirect is not https.');
        }

        if (str_starts_with($location, '/')) {
            return 'https://' . $parts['host'] . $location;
        }

        throw new RuntimeException('Remote redirect is not https.');
    }
}
