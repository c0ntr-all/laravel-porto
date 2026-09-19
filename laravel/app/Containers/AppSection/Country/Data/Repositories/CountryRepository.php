<?php declare(strict_types=1);

namespace App\Containers\AppSection\Country\Data\Repositories;

use App\Containers\AppSection\Country\Models\Country;

class CountryRepository
{
    public function firstOrCreateByName(string $name): Country
    {
        return Country::query()->firstOrCreate(['name' => $name]);
    }
}
