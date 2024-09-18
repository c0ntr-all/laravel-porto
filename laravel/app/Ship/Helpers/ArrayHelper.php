<?php declare(strict_types=1);

namespace App\Ship\Helpers;

class ArrayHelper {

    /**
     * Makes a regular array from a recursively nested array
     *
     * @param array $array
     * @return array
     */
    public static function flattenArray(array $array = []): array
    {
        static $out = [];

        foreach ($array as $subArray) {
            if (!empty($subArray['children'])) {
                $arrayToAdd = $subArray;
                unset($arrayToAdd['children']);

                $out[] = $arrayToAdd;
                self::flattenArray($subArray['children']);
            } else {
                if (isset($subArray['children'])) {
                    unset($subArray['children']);
                }

                $out[] = $subArray;
            }
        }

        return $out;
    }
}
