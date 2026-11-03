<?php

declare(strict_types=1);

namespace App\Containers\NotesSection\Note\Support;

final class NoteLinkParser
{
    /**
     * Labels written as [[название]], in order, without duplicates.
     *
     * @return list<string>
     */
    public static function labels(?string $text): array
    {
        if ($text === null || trim($text) === '') {
            return [];
        }

        preg_match_all('/\[\[([^\[\]\r\n]{1,255})\]\]/u', $text, $matches);

        $labels = [];
        $seen = [];
        foreach ($matches[1] as $label) {
            $label = trim((string) $label);
            if ($label === '') {
                continue;
            }

            $key = mb_strtolower($label);
            if (isset($seen[$key])) {
                continue;
            }

            $seen[$key] = true;
            $labels[] = $label;
        }

        return $labels;
    }
}
