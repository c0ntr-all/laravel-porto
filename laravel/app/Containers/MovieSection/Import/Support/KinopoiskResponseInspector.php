<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Support;

use Illuminate\Http\Client\Response;

class KinopoiskResponseInspector
{
    private const PREVIEW_LIMIT = 4000;

    /**
     * @param array<string, mixed> $headers
     * @return array{
     *     requested_url: string|null,
     *     final_url: string|null,
     *     http_status: int|null,
     *     content_type: string|null,
     *     html_length: int,
     *     html_preview: string,
     *     page_title: string|null,
     *     signals: array<string, mixed>,
     *     headers: array<string, string>
     * }
     */
    public function snapshot(
        string $html,
        ?string $requestedUrl = null,
        ?string $finalUrl = null,
        ?int $httpStatus = null,
        array $headers = [],
    ): array {
        return [
            'requested_url' => $requestedUrl,
            'final_url' => $finalUrl,
            'http_status' => $httpStatus,
            'content_type' => $this->header($headers, 'Content-Type'),
            'html_length' => strlen($html),
            'html_preview' => $this->preview($html),
            'page_title' => $this->pageTitle($html),
            'signals' => $this->signals($html),
            'headers' => $this->safeHeaders($headers),
        ];
    }

    public function fromResponse(Response $response, string $requestedUrl): array
    {
        $html = (string) $response->body();
        $finalUrl = $response->effectiveUri() !== null ? (string) $response->effectiveUri() : $requestedUrl;

        return $this->snapshot(
            html: $html,
            requestedUrl: $requestedUrl,
            finalUrl: $finalUrl,
            httpStatus: $response->status(),
            headers: $response->headers(),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function signals(string $html): array
    {
        $lower = mb_strtolower($html);

        return [
            'has_json_ld' => str_contains($lower, 'application/ld+json'),
            'json_ld_count' => $this->jsonLdCount($html),
            'json_ld_types' => $this->jsonLdTypes($html),
            'has_next_data' => str_contains($html, '__NEXT_DATA__'),
            'has_og_title' => str_contains($lower, 'property="og:title"') || str_contains($lower, "property='og:title'"),
            'has_og_image' => str_contains($lower, 'property="og:image"') || str_contains($lower, "property='og:image'"),
            'has_captcha' => str_contains($lower, 'showcaptcha')
                || str_contains($lower, 'smartcaptcha')
                || str_contains($lower, 'checkbox-captcha-form')
                || str_contains($lower, 'captcha__image'),
            'has_sso_install' => str_contains($lower, 'sso.kinopoisk.ru/install'),
            'looks_blocked' => str_contains($lower, 'showcaptcha')
                || str_contains($lower, 'smartcaptcha')
                || str_contains($lower, 'sso.kinopoisk.ru/install'),
        ];
    }

    public function pageTitle(string $html): ?string
    {
        if (preg_match('/<title[^>]*>(.*?)<\/title>/is', $html, $match) !== 1) {
            return null;
        }

        $title = trim(html_entity_decode(strip_tags($match[1]), ENT_QUOTES | ENT_HTML5));

        return $title !== '' ? $title : null;
    }

    public function preview(string $html): string
    {
        $normalized = trim((string) preg_replace('/\s+/u', ' ', $html));
        if (mb_strlen($normalized) <= self::PREVIEW_LIMIT) {
            return $normalized;
        }

        return mb_substr($normalized, 0, self::PREVIEW_LIMIT).'…';
    }

    /**
     * @return list<string>
     */
    public function jsonLdTypes(string $html): array
    {
        $types = [];

        foreach ($this->jsonLdDocuments($html) as $document) {
            $type = $document['@type'] ?? null;
            if (is_string($type) && $type !== '') {
                $types[] = $type;
                continue;
            }
            if (is_array($type)) {
                foreach ($type as $item) {
                    if (is_string($item) && $item !== '') {
                        $types[] = $item;
                    }
                }
            }
        }

        return array_values(array_unique($types));
    }

    public function jsonLdCount(string $html): int
    {
        return count($this->jsonLdDocuments($html));
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function jsonLdDocuments(string $html): array
    {
        if (!preg_match_all('/<script[^>]*type=["\']application\/ld\+json["\'][^>]*>(.*?)<\/script>/is', $html, $matches)) {
            return [];
        }

        $documents = [];
        foreach ($matches[1] as $json) {
            $data = json_decode(html_entity_decode(trim($json), ENT_QUOTES | ENT_HTML5), true);
            if (!is_array($data)) {
                continue;
            }

            $items = isset($data['@graph']) && is_array($data['@graph']) ? $data['@graph'] : [$data];
            foreach ($items as $item) {
                if (is_array($item)) {
                    $documents[] = $item;
                }
            }
        }

        return $documents;
    }

    /**
     * @param array<string, mixed> $headers
     * @return array<string, string>
     */
    public function safeHeaders(array $headers): array
    {
        $safe = [];
        $allowed = [
            'content-type',
            'content-length',
            'location',
            'server',
            'x-request-id',
            'x-yandex-req-id',
            'x-frame-options',
            'cache-control',
        ];

        foreach ($headers as $name => $value) {
            $key = strtolower((string) $name);
            if (!in_array($key, $allowed, true)) {
                continue;
            }

            $safe[$key] = is_array($value) ? implode(', ', array_map('strval', $value)) : (string) $value;
        }

        return $safe;
    }

    /**
     * @param array<string, mixed> $headers
     */
    private function header(array $headers, string $name): ?string
    {
        $safe = $this->safeHeaders($headers);

        return $safe[strtolower($name)] ?? null;
    }
}
