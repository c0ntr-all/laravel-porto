<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Support;

final class NotePlainText
{
    public static function fromHtml(?string $html): string
    {
        $html = (string) $html;
        if (trim($html) === '') {
            return '';
        }

        $html = preg_replace('/<\s*br\s*\/?>/i', ' ', $html) ?? $html;
        $html = preg_replace('/<\/(p|div|li|h[1-6]|blockquote|pre|tr|ul|ol)>/i', ' ', $html) ?? $html;

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\xc2\xa0", ' ', $text);

        return trim((string) preg_replace('/\s+/u', ' ', $text));
    }

    public static function excerpt(?string $text, int $limit = 180): string
    {
        $text = trim((string) $text);
        if ($limit < 4 || mb_strlen($text) <= $limit) {
            return $text;
        }

        return rtrim(mb_substr($text, 0, $limit - 3)).'...';
    }
}
