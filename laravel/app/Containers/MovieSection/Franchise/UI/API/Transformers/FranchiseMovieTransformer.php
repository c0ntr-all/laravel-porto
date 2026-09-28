<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\UI\API\Transformers;

use App\Containers\MovieSection\Movie\Models\Movie;
use App\Containers\MovieSection\Movie\UI\API\Transformers\MovieTransformer;

class FranchiseMovieTransformer extends MovieTransformer
{
    public function transform(Movie $movie): array
    {
        $data = parent::transform($movie);

        $data['order'] = isset($movie->franchise_order)
            ? (int) $movie->franchise_order
            : (isset($movie->pivot->order) ? (int) $movie->pivot->order : null);

        return $data;
    }
}
