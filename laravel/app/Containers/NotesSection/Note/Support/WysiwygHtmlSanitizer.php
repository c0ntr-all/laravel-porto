<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Support;

use DOMDocument;
use DOMElement;
use DOMNode;
use DOMText;

/**
 * Keeps the markup Quasar QEditor produces and drops scripts, event handlers,
 * and unsafe URLs. Stored HTML is rendered later, so sanitizing on write is
 * the boundary that makes a WYSIWYG field safe.
 */
final class WysiwygHtmlSanitizer
{
    private const ALLOWED_TAGS = [
        'a', 'b', 'blockquote', 'br', 'code', 'div', 'em', 'font',
        'h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'hr', 'i', 'img', 'li',
        'ol', 'p', 'pre', 's', 'span', 'strike', 'strong', 'sub', 'sup', 'u', 'ul',
    ];

    private const DROPPED_TAGS = [
        'applet', 'audio', 'base', 'button', 'embed', 'form', 'frame', 'frameset',
        'iframe', 'input', 'link', 'math', 'meta', 'noscript', 'object', 'script',
        'select', 'source', 'style', 'svg', 'textarea', 'video',
    ];

    /**
     * @var array<string, list<string>>
     */
    private const ATTRIBUTES = [
        'a' => ['href', 'target'],
        'font' => ['color', 'face', 'size'],
        'img' => ['alt', 'height', 'src', 'width'],
    ];

    /**
     * @var array<string, string>
     */
    private const FONT_FACES = [
        'arial' => 'Arial',
        'arial black' => 'Arial Black',
        'comic sans ms' => 'Comic Sans MS',
        'courier new' => 'Courier New',
        'impact' => 'Impact',
        'lucida grande' => 'Lucida Grande',
        'times new roman' => 'Times New Roman',
        'verdana' => 'Verdana',
        'serif' => 'serif',
        'sans-serif' => 'sans-serif',
        'monospace' => 'monospace',
    ];

    public function sanitize(?string $html): ?string
    {
        if ($html === null) {
            return null;
        }

        if (trim($html) === '') {
            return '';
        }

        $document = new DOMDocument;
        $internalErrors = libxml_use_internal_errors(true);

        $document->loadHTML(
            '<?xml encoding="UTF-8"><body>'.$html.'</body>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();
        libxml_use_internal_errors($internalErrors);

        $body = $document->getElementsByTagName('body')->item(0);
        if (! $body instanceof DOMElement) {
            return '';
        }

        $this->sanitizeChildren($body);

        $clean = '';
        foreach ($body->childNodes as $child) {
            $clean .= $document->saveHTML($child);
        }

        return trim($clean);
    }

    private function sanitizeChildren(DOMElement $element): void
    {
        $children = [];
        foreach ($element->childNodes as $child) {
            $children[] = $child;
        }

        foreach ($children as $child) {
            if ($child instanceof DOMElement) {
                $this->sanitizeElement($child);

                continue;
            }

            if (! $child instanceof DOMText) {
                $child->parentNode?->removeChild($child);
            }
        }
    }

    private function sanitizeElement(DOMElement $element): void
    {
        $tag = strtolower($element->nodeName);

        if (in_array($tag, self::DROPPED_TAGS, true)) {
            $element->parentNode?->removeChild($element);

            return;
        }

        $this->sanitizeChildren($element);

        if (! in_array($tag, self::ALLOWED_TAGS, true)) {
            $this->unwrap($element);

            return;
        }

        $this->filterAttributes($element, $tag);

        if ($tag === 'a' && ! $element->hasAttribute('href')) {
            $this->unwrap($element);

            return;
        }

        if ($tag === 'img' && ! $element->hasAttribute('src')) {
            $element->parentNode?->removeChild($element);
        }
    }

    private function filterAttributes(DOMElement $element, string $tag): void
    {
        $allowed = self::ATTRIBUTES[$tag] ?? [];
        $allowed[] = 'style';

        $remove = [];
        foreach ($element->attributes ?? [] as $attribute) {
            $name = strtolower($attribute->nodeName);
            if (! in_array($name, $allowed, true)) {
                $remove[] = $attribute->nodeName;

                continue;
            }

            $value = $this->sanitizeAttribute($name, (string) $attribute->nodeValue);
            if ($value === null) {
                $remove[] = $attribute->nodeName;

                continue;
            }

            if ($value !== $attribute->nodeValue) {
                $element->setAttribute($name, $value);
            }
        }

        foreach ($remove as $name) {
            $element->removeAttribute($name);
        }

        if ($tag === 'a' && strtolower($element->getAttribute('target')) === '_blank') {
            $element->setAttribute('rel', 'noopener noreferrer');
        }
    }

    private function sanitizeAttribute(string $name, string $value): ?string
    {
        return match ($name) {
            'href' => $this->safeUrl($value, allowMailto: true),
            'src' => $this->safeUrl($value, allowMailto: false),
            'target' => in_array(strtolower(trim($value)), ['_blank', '_self'], true)
                ? strtolower(trim($value))
                : null,
            'alt' => $this->plainText($value, 500),
            'style' => $this->safeStyle($value),
            'color' => $this->safeColor($value),
            'face' => self::FONT_FACES[strtolower(trim($value))] ?? null,
            'size' => preg_match('/^[1-7]$/', trim($value)) === 1 ? trim($value) : null,
            'width', 'height' => preg_match('/^\d{1,4}$/', trim($value)) === 1 ? trim($value) : null,
            default => null,
        };
    }

    private function safeUrl(string $url, bool $allowMailto): ?string
    {
        $url = trim((string) preg_replace('/[\x00-\x1F\x7F]+/', '', $url));

        if ($url === '' || preg_match('/\s/', $url) === 1 || strlen($url) > 2048) {
            return null;
        }

        $relative = str_starts_with($url, '#')
            || (str_starts_with($url, '/') && ! str_starts_with($url, '//'))
            || str_starts_with($url, '?');

        if ($relative) {
            if (! $allowMailto && (str_starts_with($url, '#') || str_starts_with($url, '?'))) {
                return null;
            }

            return $url;
        }

        $scheme = parse_url($url, PHP_URL_SCHEME);
        if (! is_string($scheme)) {
            return null;
        }

        $scheme = strtolower($scheme);
        $allowed = $allowMailto ? ['http', 'https', 'mailto'] : ['http', 'https'];

        return in_array($scheme, $allowed, true) ? $url : null;
    }

    private function safeStyle(string $style): ?string
    {
        $kept = [];

        foreach (explode(';', $style) as $declaration) {
            if (! str_contains($declaration, ':')) {
                continue;
            }

            [$property, $value] = array_map('trim', explode(':', $declaration, 2));
            $property = strtolower($property);
            $normalized = strtolower($value);

            if ($property === 'text-align' && in_array($normalized, ['left', 'center', 'right', 'justify'], true)) {
                $kept[] = 'text-align: '.$normalized;

                continue;
            }

            if (in_array($property, ['color', 'background-color'], true)) {
                $color = $this->safeColor($value);
                if ($color !== null) {
                    $kept[] = $property.': '.$color;
                }
            }
        }

        return $kept === [] ? null : implode('; ', $kept);
    }

    private function safeColor(string $value): ?string
    {
        $value = strtolower(trim($value));

        if (preg_match('/^#(?:[0-9a-f]{3}|[0-9a-f]{6}|[0-9a-f]{8})$/', $value) === 1) {
            return $value;
        }

        if (preg_match('/^rgba?\(\s*(?:\d{1,3}\s*,\s*){2}\d{1,3}(?:\s*,\s*(?:0|1|0?\.\d+))?\s*\)$/', $value) === 1) {
            return $value;
        }

        return null;
    }

    private function plainText(string $value, int $maxLength): ?string
    {
        $value = trim(strip_tags($value));
        if ($value === '') {
            return null;
        }

        return mb_substr($value, 0, $maxLength);
    }

    private function unwrap(DOMElement $element): void
    {
        $parent = $element->parentNode;
        if (! $parent instanceof DOMNode) {
            return;
        }

        while ($element->firstChild !== null) {
            $parent->insertBefore($element->firstChild, $element);
        }

        $parent->removeChild($element);
    }
}
