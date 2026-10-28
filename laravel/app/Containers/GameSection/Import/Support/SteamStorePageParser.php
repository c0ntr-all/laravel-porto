<?php

declare(strict_types=1);

namespace App\Containers\GameSection\Import\Support;

use App\Containers\GameSection\Import\Data\DTO\ParsedSteamGameDto;
use App\Containers\GameSection\Import\Exceptions\SteamStoreGameNotFoundException;
use App\Containers\GameSection\Import\Exceptions\SteamStoreParseException;
use DOMDocument;
use DOMElement;
use DOMXPath;

class SteamStorePageParser
{
    public function parse(int $steamId, string $html, string $url): ParsedSteamGameDto
    {
        $html = trim($html);
        if ($html === '') {
            throw new SteamStoreParseException('Steam store page is empty.');
        }

        $xpath = $this->xpath($html);

        if ($this->isAgeGate($xpath) && $this->appName($xpath) === null) {
            throw new SteamStoreParseException('Steam age check blocked the store page for app '.$steamId.'.');
        }

        $name = $this->appName($xpath) ?? $this->ogTitle($xpath);
        if ($name === null) {
            throw new SteamStoreGameNotFoundException($steamId);
        }

        return new ParsedSteamGameDto(
            steam_id: $steamId,
            name: $name,
            description: $this->description($xpath),
            img: $this->image($xpath),
            air_date: SteamReleaseDate::toDateString($this->releaseDate($xpath)),
            developer: $this->party($xpath, 'developer'),
            publisher: $this->party($xpath, 'publisher'),
            source_url: $url,
        );
    }

    private function xpath(string $html): DOMXPath
    {
        $previous = libxml_use_internal_errors(true);
        $dom = new DOMDocument;
        $loaded = $dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        libxml_clear_errors();
        libxml_use_internal_errors($previous);

        if ($loaded === false) {
            throw new SteamStoreParseException('Steam store page HTML could not be parsed.');
        }

        return new DOMXPath($dom);
    }

    private function isAgeGate(DOMXPath $xpath): bool
    {
        $nodes = $xpath->query("//*[contains(@id, 'agegate') or contains(@class, 'agegate')]");

        return $nodes !== false && $nodes->length > 0;
    }

    private function appName(DOMXPath $xpath): ?string
    {
        return $this->normalize($this->firstText(
            $xpath,
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' apphub_AppName ')]",
        ));
    }

    private function ogTitle(DOMXPath $xpath): ?string
    {
        $title = $this->normalize($this->metaContent($xpath, 'property', 'og:title'));
        if ($title === null) {
            return null;
        }

        $title = preg_replace('/^Save \d+% on /i', '', $title) ?? $title;
        $title = preg_replace('/ on Steam$/i', '', $title) ?? $title;
        $title = trim($title);

        if ($title === '' || in_array(mb_strtolower($title), ['steam', 'welcome to steam', 'steam store'], true)) {
            return null;
        }

        return $title;
    }

    private function description(DOMXPath $xpath): ?string
    {
        return $this->normalize($this->firstText(
            $xpath,
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' game_description_snippet ')]",
        ))
            ?? $this->normalize($this->metaContent($xpath, 'name', 'description'))
            ?? $this->normalize($this->metaContent($xpath, 'property', 'og:description'));
    }

    private function image(DOMXPath $xpath): ?string
    {
        $nodes = $xpath->query("//img[contains(concat(' ', normalize-space(@class), ' '), ' game_header_image_full ')]");
        if ($nodes !== false && $nodes->length > 0) {
            $node = $nodes->item(0);
            if ($node instanceof DOMElement) {
                $src = $this->absoluteUrl($node->getAttribute('src'));
                if ($src !== null) {
                    return $src;
                }
            }
        }

        return $this->absoluteUrl($this->metaContent($xpath, 'property', 'og:image'));
    }

    private function releaseDate(DOMXPath $xpath): ?string
    {
        return $this->normalize($this->firstText(
            $xpath,
            "//*[contains(concat(' ', normalize-space(@class), ' '), ' release_date ')]"
            ."//*[contains(concat(' ', normalize-space(@class), ' '), ' date ')]",
        ));
    }

    private function party(DOMXPath $xpath, string $label): ?string
    {
        $rows = $xpath->query("//*[contains(concat(' ', normalize-space(@class), ' '), ' dev_row ')]");
        if ($rows === false) {
            return null;
        }

        foreach ($rows as $row) {
            if (! $row instanceof DOMElement) {
                continue;
            }

            $subtitle = $this->normalize($this->firstText(
                $xpath,
                ".//*[contains(concat(' ', normalize-space(@class), ' '), ' subtitle ')]",
                $row,
            ));
            if ($subtitle === null || ! str_contains(mb_strtolower($subtitle), mb_strtolower($label))) {
                continue;
            }

            $names = [];
            $links = $xpath->query('.//a', $row);
            if ($links !== false) {
                foreach ($links as $link) {
                    $names[] = $link->textContent ?? '';
                }
            }

            if ($names === []) {
                return $this->normalize($this->firstText(
                    $xpath,
                    ".//*[contains(concat(' ', normalize-space(@class), ' '), ' summary ')]",
                    $row,
                ));
            }

            return $this->join($names);
        }

        return null;
    }

    private function metaContent(DOMXPath $xpath, string $attribute, string $value): ?string
    {
        $nodes = $xpath->query('//meta[@'.$attribute."='".$value."']");
        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        $node = $nodes->item(0);
        if (! $node instanceof DOMElement) {
            return null;
        }

        return $node->getAttribute('content');
    }

    private function firstText(DOMXPath $xpath, string $query, ?DOMElement $context = null): ?string
    {
        $nodes = $context === null ? $xpath->query($query) : $xpath->query($query, $context);
        if ($nodes === false || $nodes->length === 0) {
            return null;
        }

        return $nodes->item(0)?->textContent;
    }

    /**
     * @param  list<string>  $names
     */
    private function join(array $names): ?string
    {
        $clean = [];
        foreach ($names as $name) {
            $normalized = $this->normalize($name);
            if ($normalized !== null && ! in_array($normalized, $clean, true)) {
                $clean[] = $normalized;
            }
        }

        if ($clean === []) {
            return null;
        }

        return implode(', ', $clean);
    }

    private function absoluteUrl(?string $url): ?string
    {
        $url = $this->normalize($url);
        if ($url === null) {
            return null;
        }

        if (str_starts_with($url, '//')) {
            return 'https:'.$url;
        }

        return $url;
    }

    private function normalize(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = trim(preg_replace('/\s+/u', ' ', $value) ?? '');

        return $value === '' ? null : $value;
    }
}
