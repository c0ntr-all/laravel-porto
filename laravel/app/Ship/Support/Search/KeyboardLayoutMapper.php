<?php declare(strict_types=1);

namespace App\Ship\Support\Search;

/**
 * Converts text typed in the wrong keyboard layout (QWERTY ↔ ЙЦУКЕН).
 * This is not transliteration (Tor ↔ Тор) - only same-key layout flipping.
 */
final class KeyboardLayoutMapper
{
    private const EN_TO_RU = [
        'q' => 'й', 'w' => 'ц', 'e' => 'у', 'r' => 'к', 't' => 'е', 'y' => 'н', 'u' => 'г',
        'i' => 'ш', 'o' => 'щ', 'p' => 'з', '[' => 'х', ']' => 'ъ',
        'a' => 'ф', 's' => 'ы', 'd' => 'в', 'f' => 'а', 'g' => 'п', 'h' => 'р',
        'j' => 'о', 'k' => 'л', 'l' => 'д', ';' => 'ж', '\'' => 'э',
        'z' => 'я', 'x' => 'ч', 'c' => 'с', 'v' => 'м', 'b' => 'и', 'n' => 'т',
        'm' => 'ь', ',' => 'б', '.' => 'ю', '`' => 'ё',
        'Q' => 'Й', 'W' => 'Ц', 'E' => 'У', 'R' => 'К', 'T' => 'Е', 'Y' => 'Н', 'U' => 'Г',
        'I' => 'Ш', 'O' => 'Щ', 'P' => 'З', '{' => 'Х', '}' => 'Ъ',
        'A' => 'Ф', 'S' => 'Ы', 'D' => 'В', 'F' => 'А', 'G' => 'П', 'H' => 'Р',
        'J' => 'О', 'K' => 'Л', 'L' => 'Д', ':' => 'Ж', '"' => 'Э',
        'Z' => 'Я', 'X' => 'Ч', 'C' => 'С', 'V' => 'М', 'B' => 'И', 'N' => 'Т',
        'M' => 'Ь', '<' => 'Б', '>' => 'Ю', '~' => 'Ё',
    ];

    /** @var array<string, string>|null */
    private static ?array $ruToEn = null;

    public function flip(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        $enToRu = self::EN_TO_RU;
        $ruToEn = self::ruToEn();
        $result = '';

        $length = mb_strlen($value);
        for ($i = 0; $i < $length; $i++) {
            $char = mb_substr($value, $i, 1);

            if (isset($enToRu[$char])) {
                $result .= $enToRu[$char];
                continue;
            }

            if (isset($ruToEn[$char])) {
                $result .= $ruToEn[$char];
                continue;
            }

            $result .= $char;
        }

        return $result;
    }

    /**
     * @return list<string>
     */
    public function variants(string $value): array
    {
        $value = trim($value);
        if ($value === '') {
            return [];
        }

        $flipped = $this->flip($value);
        if ($flipped === $value) {
            return [$value];
        }

        return [$value, $flipped];
    }

    /**
     * @return array<string, string>
     */
    private static function ruToEn(): array
    {
        return self::$ruToEn ??= array_flip(self::EN_TO_RU);
    }
}
