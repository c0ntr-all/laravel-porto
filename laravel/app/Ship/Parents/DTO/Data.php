<?php declare(strict_types=1);

namespace App\Ship\Parents\DTO;

use Spatie\LaravelData\Data as SpatieLaravelData;

class Data extends SpatieLaravelData
{
    /**
     * For partial updating resources
     *
     * @return array
     */
    public function toUpdatableArray(): array
    {
        return array_filter(
            get_object_vars($this),
            fn ($value) => !empty($value)
        );
    }
}
