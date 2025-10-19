<?php declare(strict_types=1);

namespace App\Ship\Helpers;

class StringHelper {

    /**
     * Generates default unique name for file
     * TODO: Надо перенести в PathGenerationService т.к. это специфичное для бизнес логики формирование имени файла
     *
     * @param string|null $extension
     * @return string
     * @throws \Random\RandomException
     */
    public static function generateFilename(string $extension = null): string
    {
        $filename = uniqid(bin2hex(random_bytes(8)));

        if ($extension) {
            $filename .= '.' . $extension;
        }

        return $filename;
    }
}
