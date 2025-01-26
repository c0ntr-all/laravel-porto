<?php declare(strict_types=1);

namespace App\Ship\Parents\DTO;

use Spatie\LaravelData\Data as SpatieLaravelData;

class Data extends SpatieLaravelData
{
    /**
     * For partial updating resources
     *
     * @param array $nullableFields
     * @return array
     */
    public function toUpdatableArray(array $nullableFields = []): array
    {
        return array_filter(
            get_object_vars($this),
            fn($value, $key) => $value !== null || in_array($key, $nullableFields, true), // Except nullable fields
            ARRAY_FILTER_USE_BOTH
        );
    }
}
