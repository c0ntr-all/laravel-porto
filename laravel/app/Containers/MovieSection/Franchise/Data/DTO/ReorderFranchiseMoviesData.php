<?php declare(strict_types=1);

namespace App\Containers\MovieSection\Franchise\Data\DTO;

use App\Ship\Parents\DTO\Data;

class ReorderFranchiseMoviesData extends Data
{
    /** @var list<int> */
    public array $movie_ids;

    public function __construct()
    {
    }
}
