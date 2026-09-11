<?php declare(strict_types=1);

namespace App\Containers\AppSection\Country\UI\API\Transformers;

use App\Containers\AppSection\Country\Models\Country;
use League\Fractal\TransformerAbstract;

class CountryTransformer extends TransformerAbstract
{
    public function transform(Country $country): array
    {
        return [
            'id' => $country->id,
            'name' => $country->name,
            'description' => $country->description,
            'image' => $country->image,
        ];
    }
}
