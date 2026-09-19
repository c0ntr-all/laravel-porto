<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Import\Tasks;

use App\Containers\AppSection\Country\Data\Repositories\CountryRepository;
use App\Containers\AppSection\Country\Models\Country;
use App\Ship\Parents\Tasks\Task as ParentTask;

class FindOrCreateCountriesFromParsedTask extends ParentTask
{
    public function __construct(
        private readonly CountryRepository $countryRepository,
    ) {
    }

    /**
     * @param list<string> $countryNames
     * @return list<Country>
     */
    public function run(array $countryNames): array
    {
        $models = [];

        foreach ($countryNames as $name) {
            $name = trim($name);
            if ($name === '') {
                continue;
            }

            $models[] = $this->countryRepository->firstOrCreateByName($name);
        }

        return $models;
    }
}
