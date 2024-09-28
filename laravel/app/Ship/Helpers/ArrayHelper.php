<?php declare(strict_types=1);

namespace App\Ship\Helpers;

class ArrayHelper {

    /**
     * Makes a regular array from a recursively nested array
     *
     * @param array $array
     * @param string $keyToFlat
     * @return array
     */
    public static function flattenArray(array $array = [], string $keyToFlat = 'children'): array
    {
        static $out = [];

        foreach ($array as $subArray) {
            if (!empty($subArray[$keyToFlat])) {
                $arrayToAdd = $subArray;
                unset($arrayToAdd[$keyToFlat]);

                $out[] = $arrayToAdd;
                self::flattenArray($subArray[$keyToFlat]);
            } else {
                if (isset($subArray[$keyToFlat])) {
                    unset($subArray[$keyToFlat]);
                }

                $out[] = $subArray;
            }
        }

        return $out;
    }
}
